<?php

namespace Urbem\AdministrativoBundle\Resources\config\Sonata\Cgm;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\DoctrineORMAdminBundle\Model\ModelManager;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

use Urbem\CoreBundle\Entity\SwAtributoCgm;
use Urbem\CoreBundle\Entity\SwBairro;
use Urbem\CoreBundle\Entity\SwBairroLogradouro;
use Urbem\CoreBundle\Entity\SwCep;
use Urbem\CoreBundle\Entity\SwCepLogradouro;
use Urbem\CoreBundle\Entity\SwCgm;
use Urbem\CoreBundle\Entity\SwCgmAtributoValor;
use Urbem\CoreBundle\Entity\SwCgmLogradouro;
use Urbem\CoreBundle\Entity\SwCgmLogradouroCorrespondencia;
use Urbem\CoreBundle\Entity\SwLogradouro;
use Urbem\CoreBundle\Entity\SwPais;
use Urbem\CoreBundle\Helper\StringHelper;
use Urbem\CoreBundle\Model\SwCgmModel;
use Urbem\CoreBundle\Resources\config\Sonata\AbstractSonataAdmin;

/**
 * Class SwCgmAdmin
 *
 * @package    Urbem\AdministrativoBundle\Resources\config\Sonata\Cgm
 */
abstract class SwCgmAdmin extends AbstractSonataAdmin
{
    const ATTR_DINAMICO_FIELD_PREFIX = 'atributo_dinamico_';

    /** @var SwCgm|null */
    protected $swCgm = null;

    /**
     * @param integer $length
     *
     * @return Assert\Length
     */
    protected function addConstraintLength($length)
    {
        return new Assert\Length([
            'max'        => $length,
            'maxMessage' => $this->trans('default.errors.maxMessage', [], 'validators')
        ]);
    }

    /**
     * Renderiza campos de:
     * - Dados de Endereço do CGM.
     * - Dados de Endereço para Correspondencia do CGM.
     *
     * @param FormMapper $formMapper
     *
     * @return $this
     */
    protected function configureFormFieldsDadosEndereco(FormMapper $formMapper)
    {
        $this->setIncludeJs(array_merge($this->includeJs, [
            '/administrativo/javascripts/cgm/swcgm/init.js',
            '/administrativo/javascripts/cgm/swcgm/form.js',
            '/administrativo/javascripts/cgm/swcgm/form--logradouro.js'
        ]));

        /** @var ModelManager $modelManager */
        $modelManager = $this->getModelManager();
        $entityManager = $modelManager->getEntityManager($this->getClass());

        /** @var SwPais $swPais */
        $swPais = $modelManager->find(SwPais::class, 1);

        $fieldOptionsDefaultCustomTemplateClasses = 'form_row col s3 campo-sonata';
        $fieldOptionsDefaultCustomTemplate = [
            'label'    => false,
            'mapped'   => false,
            'template' => 'CoreBundle:Sonata\CRUD:edit_generic.html.twig'
        ];

        $fieldOptions = [];
        $fieldOptions['fkSwPais'] = [
            'attr'   => ['class' => 'select2-parameters'],
            'class'  => SwPais::class,
            'data'   => $swPais,
            'label'  => 'label.pais',
            'mapped' => false
        ];

        $fieldOptions['fkSwMunicipio.fkSwUf'] = array_merge($fieldOptionsDefaultCustomTemplate, [
            'attr' => ['class' => $fieldOptionsDefaultCustomTemplateClasses . ' fkSwMunicipio_fkSwUf'],
            'data' => [
                'label' => 'label.swLogradouro.estado',
                'value' => null
            ]
        ]);

        $fieldOptions['fkSwMunicipio'] = array_merge($fieldOptionsDefaultCustomTemplate, [
            'attr' => ['class' => $fieldOptionsDefaultCustomTemplateClasses . ' fkSwMunicipio'],
            'data' => [
                'label' => 'label.cidade',
                'value' => null
            ]
        ]);

        $fieldOptions['swLogradouro'] = [
            'attr'                 => ['class' => 'select2-parameters '],
            'class'                => SwLogradouro::class,
            'json_from_admin_code' => $this->getCode(),
            'json_query_builder'   => function (EntityRepository $repository, $term, Request $request) {
                $codPais = $request->get('codPais');

                $queryBuilder = $repository->createQueryBuilder('l');

                // Like
                $queryBuilder
                    ->join("l.fkSwNomeLogradouros", "n")
                    ->where("lower(n.nomLogradouro) LIKE lower(:term)")
                    ->setParameter("term", "%{$term}%");

                // Pais / Uf / Municipio
                $queryBuilder
                    ->join("l.fkSwMunicipio", "m")
                    ->join("m.fkSwUf", "u")
                    ->andWhere("u.codPais = :codPais")
                    ->setParameter("codPais", $codPais);

                return $queryBuilder;
            },
            'req_params'           => ['codPais' => 'varJsCodPais'],
            'label'                => 'swLogradouro',
            'mapped'               => false,
            'placeholder'          => $this->trans('label.selecione')
        ];

        $fieldOptions['numero'] = [
            'attr'        => ['maxlength' => 6],
            'constraints' => [$this->addConstraintLength(6)],
            'label'       => 'label.numero',
            'mapped'      => false
        ];

        $fieldOptions['complemento'] = [
            'attr'        => ['maxlength' => 30],
            'constraints' => [$this->addConstraintLength(30)],
            'label'       => 'label.complemento',
            'mapped'      => false,
            'required'    => false
        ];

        $fieldOptions['swBairro'] = [
            'attr'         => ['class' => 'select2-parameters '],
            'class'        => SwBairro::class,
            'choice_value' => function ($object) use ($modelManager) {
                if ($object instanceof SwBairro) {
                    return $modelManager->getUrlsafeIdentifier($object);
                }

                return null;
            },
            'label'        => 'label.swLogradouro.bairro',
            'mapped'       => false,
            'placeholder'  => 'label.selecione'
        ];

        $fieldOptions['swCep'] = [
            'attr'         => ['class' => 'select2-parameters '],
            'class'        => SwCep::class,
            'choice_value' => function ($object) use ($modelManager) {
                return $modelManager->getNormalizedIdentifier($object);
            },
            'label'        => 'label.swLogradouro.cep',
            'mapped'       => false,
            'placeholder'  => 'label.selecione'
        ];

        if (!is_null($this->swCgm)) {
            $fieldOptions['fkSwPais']['data'] = $this->swCgm->getFkSwPais();
            $fieldOptions['fkSwMunicipio.fkSwUf']['data']['value'] = $this->swCgm->getFkSwMunicipio()->getFkSwUf();
            $fieldOptions['fkSwMunicipio']['data']['value'] = $this->swCgm->getFkSwMunicipio();

            $fieldOptions['numero']['data'] = $this->swCgm->getNumero();
            $fieldOptions['complemento']['data'] = $this->swCgm->getComplemento();
            $fieldOptions['swBairro']['data'] = $this->swCgm->getNumero();

            /** @var SwCgmLogradouro $swCgmLogradouro */
            $swCgmLogradouro = $this->swCgm->getFkSwCgmLogradouros()->last();

            if ($swCgmLogradouro) {
                $fieldOptions['swBairro']['data'] = $swCgmLogradouro->getFkSwBairroLogradouro()->getFkSwBairro();
                $fieldOptions['swCep']['data'] = $swCgmLogradouro->getFkSwCepLogradouro()->getFkSwCep();

                /** @var SwLogradouro $swLogradouro */
                $swLogradouro = $entityManager->find(SwLogradouro::class, $swCgmLogradouro->getCodLogradouro());
                $fieldOptions['swLogradouro']['data'] = $swLogradouro;
            }
        }

        $formMapper
            ->with('label.swCgm.dadosEndereco')
            ->add('fkSwCgm.fkSwPais', 'entity', $fieldOptions['fkSwPais'])
            ->add('fkSwMunicipio.fkSwUf', 'customField', $fieldOptions['fkSwMunicipio.fkSwUf'])
            ->add('fkSwMunicipio', 'customField', $fieldOptions['fkSwMunicipio'])
            ->add('swLogradouro', 'autocomplete', $fieldOptions['swLogradouro'])
            ->add('numero', 'text', $fieldOptions['numero'])
            ->add('complemento', 'text', $fieldOptions['complemento'])
            ->add('swBairro', 'entity', $fieldOptions['swBairro'])
            ->add('swCep', 'entity', $fieldOptions['swCep'])
            ->end();

        $fieldOptions['fkSwPais1'] = array_merge($fieldOptions['fkSwPais'], [
            'required' => false
        ]);

        $fieldOptions['fkSwMunicipio1.fkSwUf'] = array_merge($fieldOptions['fkSwMunicipio.fkSwUf'], [
            'attr' => ['class' => $fieldOptions['fkSwMunicipio.fkSwUf']['attr']['class'] . '1'],
        ]);

        $fieldOptions['fkSwMunicipio1'] = array_merge($fieldOptions['fkSwMunicipio'], [
            'attr' => ['class' => $fieldOptions['fkSwMunicipio']['attr']['class'] . '1'],
        ]);

        $fieldOptions['swLogradouroCorresp'] = array_merge($fieldOptions['swLogradouro'], [
            'required'   => false,
            'req_params' => ['codPais' => 'varJsCodPaisCorresp'],
        ]);

        $fieldOptions['numeroCorresp'] = array_merge($fieldOptions['numero'], [
            'required' => false
        ]);

        $fieldOptions['complementoCorresp'] = array_merge($fieldOptions['complemento'], [
            'required' => false
        ]);

        $fieldOptions['swBairroCorresp'] = array_merge($fieldOptions['swBairro'], [
            'required' => false
        ]);

        $fieldOptions['swCepCorresp'] = array_merge($fieldOptions['swCep'], [
            'required' => false
        ]);

        if (!is_null($this->swCgm)) {
            $swPaisCorresp = $this->swCgm->getFkSwPais1();
            $swMunicipioCorresp = $this->swCgm->getFkSwMunicipio1();

            if (!is_null($swMunicipioCorresp)) {
                $fieldOptions['fkSwPais1']['data'] = $swPaisCorresp;
                $fieldOptions['fkSwMunicipio1']['data']['value'] = $swMunicipioCorresp;
                $fieldOptions['fkSwMunicipio1.fkSwUf']['data']['value'] = $swMunicipioCorresp->getFkSwUf();

                $fieldOptions['numeroCorresp']['data'] = $this->swCgm->getNumeroCorresp();
                $fieldOptions['complementoCorresp']['data'] = $this->swCgm->getComplementoCorresp();
                $fieldOptions['swBairroCorresp']['data'] = $this->swCgm->getNumeroCorresp();
                $fieldOptions['numeroCorresp']['data'] = $this->swCgm->getNumeroCorresp();

                /** @var SwCgmLogradouroCorrespondencia $swCgmLogradouroCorrespondencia */
                $swCgmLogradouroCorrespondencia = $this->swCgm->getFkSwCgmLogradouroCorrespondencias()->last();

                if ($swCgmLogradouroCorrespondencia) {
                    $fieldOptions['swBairroCorresp']['data'] = $swCgmLogradouroCorrespondencia->getFkSwBairroLogradouro()->getFkSwBairro();
                    $fieldOptions['swCepCorresp']['data'] = $swCgmLogradouroCorrespondencia->getFkSwCepLogradouro()->getFkSwCep();

                    /** @var SwLogradouro $swLogradouro */
                    $swLogradouro = $entityManager->find(SwLogradouro::class, $swCgmLogradouroCorrespondencia->getCodLogradouro());
                    $fieldOptions['swLogradouroCorresp']['data'] = $swLogradouro;
                }
            } else {
                $fieldOptions['numeroCorresp']['data'] = null;
                $fieldOptions['complementoCorresp']['data'] = null;
                $fieldOptions['swBairroCorresp']['data'] = null;
                $fieldOptions['numeroCorresp']['data'] = null;
                $fieldOptions['swCepCorresp']['data'] = null;
                $fieldOptions['swLogradouroCorresp']['data'] = null;
                $fieldOptions['fkSwMunicipio1']['data']['value'] = "";
                $fieldOptions['fkSwMunicipio1.fkSwUf']['data']['value'] = "";
            }
        }

        $formMapper
            ->with('label.swCgm.dadosEntedecoCorrespondencia')
            ->add('fkSwPais1', 'entity', $fieldOptions['fkSwPais1'])
            ->add('fkSwMunicipio1.fkSwUf', 'customField', $fieldOptions['fkSwMunicipio1.fkSwUf'])
            ->add('fkSwMunicipio1', 'customField', $fieldOptions['fkSwMunicipio1'])
            ->add('swLogradouroCorresp', 'autocomplete', $fieldOptions['swLogradouroCorresp'])
            ->add('numeroCorresp', 'text', $fieldOptions['numeroCorresp'])
            ->add('complementoCorresp', 'text', $fieldOptions['complementoCorresp'])
            ->add('swBairroCorresp', 'entity', $fieldOptions['swBairroCorresp'])
            ->add('swCepCorresp', 'entity', $fieldOptions['swCepCorresp'])
            ->end();

        return $this;
    }

    /**
     * Renderiza campos de:
     * - Dados de Contato do CGM.
     *
     * @param FormMapper $formMapper
     *
     * @return $this
     */
    protected function configureFormFieldsDadosContato(FormMapper $formMapper)
    {
        $fieldOptions = [];
        $fieldOptions['foneResidencial'] = [
            'attr'     => ['class' => 'fone '],
            'label'    => 'label.telefone_residencial',
            'mapped'   => false,
            'required' => false,
        ];

        $fieldOptions['foneComercial'] = [
            'attr'     => ['class' => 'fone '],
            'label'    => 'label.telefone_comercial',
            'mapped'   => false,
            'required' => false,
        ];

        $fieldOptions['ramalComecial'] = [
            'attr'        => ['maxlength' => 6],
            'constraints' => [$this->addConstraintLength(6)],
            'label'       => 'label.ramal',
            'mapped'      => false,
            'required'    => false,
        ];

        $fieldOptions['foneCelular'] = [
            'attr'     => ['class' => 'fone '],
            'label'    => 'label.telefone_celular',
            'mapped'   => false,
            'required' => false,
        ];

        $fieldOptions['eMail'] = [
            'attr'        => ['maxlength' => 100],
            'constraints' => [
                $this->addConstraintLength(100),
                new Assert\Email([
                    'message' => $this->trans('default.errors.invalidEmail', [], 'validators')
                ])
            ],
            'label'       => 'email',
            'mapped'      => false,
            'required'    => false,
        ];

        $fieldOptions['eMailAdcional'] = [
            'attr'        => ['maxlength' => 100],
            'constraints' => [
                $this->addConstraintLength(100),
                new Assert\Email([
                    'message' => $this->trans('default.errors.invalidEmail', [], 'validators')
                ])
            ],
            'label'       => 'label.email_adicional',
            'mapped'      => false,
            'required'    => false,
        ];

        $fieldOptions['site'] = [
            'attr'        => ['maxlength' => 100],
            'constraints' => [$this->addConstraintLength(100)],
            'label'       => 'label.site',
            'mapped'      => false,
            'required'    => false,
        ];

        if (!is_null($this->swCgm)) {
            $fieldOptions['foneResidencial']['data'] = $this->swCgm->getFoneResidencial();
            $fieldOptions['foneComercial']['data'] = $this->swCgm->getFoneComercial();
            $fieldOptions['ramalComecial']['data'] = abs($this->swCgm->getRamalComercial());
            $fieldOptions['foneCelular']['data'] = $this->swCgm->getFoneCelular();
            $fieldOptions['eMail']['data'] = $this->swCgm->getEMail();
            $fieldOptions['eMailAdcional']['data'] = $this->swCgm->getEMailAdcional();
            $fieldOptions['site']['data'] = $this->swCgm->getSite();
        }

        $formMapper
            ->with('label.swCgm.dadosContato')
            ->add('foneResidencial', 'text', $fieldOptions['foneResidencial'])
            ->add('foneComercial', 'text', $fieldOptions['foneComercial'])
            ->add('ramalComercial', 'number', $fieldOptions['ramalComecial'])
            ->add('foneCelular', 'text', $fieldOptions['foneCelular'])
            ->add('eMail', 'email', $fieldOptions['eMail'])
            ->add('eMailAdcional', 'email', $fieldOptions['eMailAdcional'])
            ->add('site', 'text', $fieldOptions['site'])
            ->end();

        return $this;
    }

    /**
     * Renderiza campos de:
     * - Dados de Atributo Dinamico por tipo do campo.
     *
     * @param FormMapper    $formMapper
     * @param SwAtributoCgm $swAtributoCgm
     */
    private function configureFormFieldAttrDinamico(FormMapper $formMapper, SwAtributoCgm $swAtributoCgm)
    {
        $defaultOptions = [
            'data'     => $swAtributoCgm->getValorPadrao(),
            'label'    => ucwords($swAtributoCgm->getNomAtributo()),
            'mapped'   => false,
            'required' => false
        ];

        $fieldName = self::ATTR_DINAMICO_FIELD_PREFIX . $swAtributoCgm->getCodAtributo();

        if (!is_null($this->swCgm)) {

            /** @var ModelManager $modelManager */
            $modelManager = $this->getModelManager();

            /** @var SwCgmAtributoValor $swCgmAtributoValor */
            $swCgmAtributoValor = $modelManager->findOneBy(SwCgmAtributoValor::class, [
                'codAtributo' => $swAtributoCgm->getCodAtributo(),
                'numcgm'      => $this->swCgm->getNumcgm()
            ]);

            if ($swCgmAtributoValor instanceof SwCgmAtributoValor) {
                $defaultOptions['data'] = $swCgmAtributoValor->getValor();
            }
        }

        switch ($swAtributoCgm->getTipo()) {
            case 't':
                $formMapper->add($fieldName, 'text', $defaultOptions);

                break;
            case 'n':
                $formMapper->add($fieldName, 'number', array_merge($defaultOptions, [
                    'data' => abs($defaultOptions['data'])
                ]));

                break;
            case 'l':
                $arrayData = explode("\r\n", $swAtributoCgm->getValorPadrao());

                $choices = [];
                foreach ($arrayData as $choice) {
                    if (!empty($choice)) {
                        $choices[$choice] = $choice;
                    }
                }

                $formMapper->add($fieldName, 'choice', array_merge($defaultOptions, [
                    'attr'        => ['class' => 'select2-parameters '],
                    'choices'     => $choices,
                    'placeholder' => 'label.selecione'
                ]));
        }
    }

    /**
     * Renderiza campos de:
     * - Dados de Atributo Dinamico.
     *
     * @param FormMapper $formMapper
     *
     * @return $this
     */
    protected function configureFormFieldsAtributoDinamico(FormMapper $formMapper)
    {
        /** @var ModelManager $modelManager */
        $modelManager = $this->getModelManager();

        $swAtributoCgmArray = $modelManager->findBy(SwAtributoCgm::class, []);

        $formMapper->with('label.swCgm.atributos');

        /** @var SwAtributoCgm $swAtributoCgm */
        foreach ($swAtributoCgmArray as $swAtributoCgm) {
            $this->configureFormFieldAttrDinamico($formMapper, $swAtributoCgm);
        }

        $formMapper->end();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $this
            ->configureFormFieldsDadosEndereco($formMapper)
            ->configureFormFieldsDadosContato($formMapper)
            ->configureFormFieldsAtributoDinamico($formMapper);
    }

    /**
     * Inicia a persistencia dos campos de SwCgm enviados pelo form.
     *
     * @param SwCgm $swCgm
     */
    protected function prePersistSwCgm(SwCgm $swCgm)
    {
        /** @var EntityManager $entityManager */
        $entityManager = $this->getModelManager()->getEntityManager($this->getClass());

        $swCgmModel = new SwCgmModel($entityManager);
        $swCgmModel->setNumcgm($swCgm);

        $this
            ->prePersistResponsavel($swCgm)
            ->prePersistDadosEndereco($swCgm)
            ->prePersistDadosEnderecoCorresp($swCgm)
            ->prePersistDadosContato($swCgm)
            ->prePersistAtributoValores($swCgm);

        $swCgm->setTimestampInclusao(new \DateTime());

        $entityManager->persist($swCgm);
    }

    /**
     * Inicia a edição dos dados de SwCgm enviados pelo form.
     *
     * @param SwCgm $swCgm
     */
    protected function preUpdateSwCgm(SwCgm $swCgm)
    {
        /** @var EntityManager $entityManager */
        $entityManager = $this->getModelManager()->getEntityManager($this->getClass());

        $this
            ->prePersistDadosEnderecoCorresp($swCgm, true)
            ->prePersistDadosContato($swCgm)
            ->prePersistAtributoValores($swCgm, true);

        $swCgm->setTimestampAlteracao(new \DateTime());

        $entityManager->persist($swCgm);
    }

    /**
     * Recupera usuario logado e o cadastra como responsavel pelo CGM cadastrado.
     *
     * @param SwCgm $swCgm
     *
     * @return $this
     */
    private function prePersistResponsavel(SwCgm $swCgm)
    {
        $usuario = $this->getCurrentUser();
        $numcgmResponvavel = $usuario->getFkSwCgm()->getNumcgm();

        $swCgm->setCodResponsavel($numcgmResponvavel);

        return $this;
    }

    /**
     * Adiciona os campos de Endereço ao objeto SwCgm.
     *
     * @param SwCgm $swCgm
     *
     * @return $this
     */
    private function prePersistDadosEndereco(SwCgm $swCgm)
    {
        $form = $this->getForm();

        /** @var SwPais $swPais */
        $swPais = $form->get('fkSwCgm__fkSwPais')->getData();

        /** @var SwLogradouro $swLogradouro */
        $swLogradouro = $form->get('swLogradouro')->getData();

        /** @var SwBairro $swBairro */
        $swBairro = $form->get('swBairro')->getData();

        /** @var SwCep $swCep */
        $swCep = $form->get('swCep')->getData();

        /** @var SwBairroLogradouro $swBairroLogradouro */
        $swBairroLogradouro = $swLogradouro
            ->getFkSwBairroLogradouros()
            ->filter(function (SwBairroLogradouro $swBairroLogradouro) use ($swBairro) {
                return $swBairroLogradouro->getFkSwBairro() === $swBairro;
            })
            ->first();

        /** @var SwCepLogradouro $swCepLogradouro */
        $swCepLogradouro = $swLogradouro
            ->getFkSwCepLogradouros()->filter(function (SwCepLogradouro $swCepLogradouro) use ($swCep) {
                return $swCepLogradouro->getFkSwCep() === $swCep;
            })
            ->first();

        $swCgmLogradouro = (new SwCgmLogradouro())
            ->setFkSwBairroLogradouro($swBairroLogradouro)
            ->setFkSwCepLogradouro($swCepLogradouro)
            ->setFkSwCgm($swCgm);

        $tipo = $swLogradouro->getCurrentFkSwNomeLogradouro()->getFkSwTipoLogradouro()->getNomTipo();
        $logradouro = $swLogradouro->getCurrentFkSwNomeLogradouro()->getNomLogradouro();
        $numero = $form->get('numero')->getData();
        $complemento = $form->get('complemento')->getData();
        $bairro = $swBairro->getNomBairro();
        $cep = $swCep->getCep();

        $swCgm
            ->setFkSwPais($swPais)
            ->setFkSwMunicipio($swLogradouro->getFkSwMunicipio())
            ->setLogradouro($logradouro)
            ->setNumero($numero)
            ->setComplemento($complemento)
            ->setBairro($bairro)
            ->setCep($cep)
            ->setTipoLogradouro($tipo);

        $swCgm->addFkSwCgmLogradouros($swCgmLogradouro);

        return $this;
    }

    /**
     * Adiciona os campos de Endereço para Correspondencia ao objeto SwCgm.
     *
     * @param SwCgm $swCgm
     * @param bool  $isUpdate
     *
     * @return $this
     */
    private function prePersistDadosEnderecoCorresp(SwCgm $swCgm, $isUpdate = false)
    {
        $form = $this->getForm();

        /** @var SwPais $swPaisCorresp */
        $swPaisCorresp = $form->get('fkSwPais1')->getData();

        /** @var SwLogradouro $swLogradouroCorresp */
        $swLogradouroCorresp = $form->get('swLogradouroCorresp')->getData();

        /** @var SwBairro $swBairroCorresp */
        $swBairroCorresp = $form->get('swBairroCorresp')->getData();

        /** @var SwCep $swCepCorresp */
        $swCepCorresp = $form->get('swCepCorresp')->getData();

        if (!is_null($swLogradouroCorresp) && !is_null($swBairroCorresp)) {
            /** @var SwBairroLogradouro $swBairroLogradouroCorresp */
            $swBairroLogradouroCorresp = $swLogradouroCorresp
                ->getFkSwBairroLogradouros()
                ->filter(function (SwBairroLogradouro $swBairroLogradouroCorresp) use ($swBairroCorresp) {
                    return $swBairroLogradouroCorresp->getFkSwBairro() === $swBairroCorresp;
                })
                ->first();

            /** @var SwCepLogradouro $swCepLogradouroCorresp */
            $swCepLogradouroCorresp = $swLogradouroCorresp
                ->getFkSwCepLogradouros()
                ->filter(function (SwCepLogradouro $swCepLogradouroCorresp) use ($swCepCorresp) {
                    return $swCepLogradouroCorresp->getFkSwCep() === $swCepCorresp;
                })
                ->first();

            $swCgmLogradouroCorrespondencia = $swCgm->getFkSwCgmLogradouroCorrespondencias()->last();

            if (false === $isUpdate || true === empty($swCgmLogradouroCorrespondencia)) {
                $swCgmLogradouroCorrespondencia = (new SwCgmLogradouroCorrespondencia())->setFkSwCgm($swCgm);
            }

            $swCgmLogradouroCorrespondencia
                ->setFkSwBairroLogradouro($swBairroLogradouroCorresp)
                ->setFkSwCepLogradouro($swCepLogradouroCorresp);

            $tipoCorresp = $swLogradouroCorresp->getCurrentFkSwNomeLogradouro()->getFkSwTipoLogradouro()->getNomTipo();
            $logradouroCorresp = $swLogradouroCorresp->getCurrentFkSwNomeLogradouro()->getNomLogradouro();
            $bairroCorresp = $swBairroCorresp->getNomBairro();
            $cepCorresp = $swCepCorresp->getCep();

            $swCgm
                ->setFkSwPais1($swPaisCorresp)
                ->setFkSwMunicipio1($swLogradouroCorresp->getFkSwMunicipio())
                ->setLogradouroCorresp($logradouroCorresp)
                ->setBairroCorresp($bairroCorresp)
                ->setCepCorresp($cepCorresp)
                ->setTipoLogradouroCorresp($tipoCorresp);

            if ($isUpdate) {
                /** @var EntityManager $entityManager */
                $entityManager = $this->getModelManager()->getEntityManager($this->getClass());

                $entityManager->persist($swCgmLogradouroCorrespondencia);
            } else {
                $swCgm->addFkSwCgmLogradouroCorrespondencias($swCgmLogradouroCorrespondencia);
            }
        }

        $numeroCorresp = $form->get('numeroCorresp')->getData();
        $complementoCorresp = $form->get('complementoCorresp')->getData();

        $swCgm
            ->setNumeroCorresp($numeroCorresp)
            ->setComplementoCorresp($complementoCorresp);

        return $this;
    }

    /**
     * Adiciona os campos de Contato ao objeto SwCgm.
     *
     * @param SwCgm $swCgm
     *
     * @return $this
     */
    private function prePersistDadosContato(SwCgm $swCgm)
    {
        $form = $this->getForm();

        $foneResidencial = $form->get('foneResidencial')->getData();
        if (!is_null($foneResidencial)) {
            $foneResidencial = StringHelper::clearString($foneResidencial);
            $foneResidencial = StringHelper::removeAllSpace($foneResidencial);

            $swCgm->setFoneResidencial($foneResidencial);
        }

        $foneComercial = $form->get('foneComercial')->getData();
        if (!is_null($foneComercial)) {
            $foneComercial = StringHelper::clearString($foneComercial);
            $foneComercial = StringHelper::removeAllSpace($foneComercial);

            $swCgm->setFoneComercial($foneComercial);
        }

        $ramalComercial = $form->get('ramalComercial')->getData();
        if (!is_null($ramalComercial)) {
            $ramalComercial = abs($ramalComercial);

            $swCgm->setRamalComercial($ramalComercial);
        }

        $foneCelular = $form->get('foneCelular')->getData();
        if (!is_null($foneCelular)) {
            $foneCelular = StringHelper::clearString($foneCelular);
            $foneCelular = StringHelper::removeAllSpace($foneCelular);

            $swCgm->setFoneCelular($foneCelular);
        }

        $email = $form->get('eMail')->getData();
        $swCgm->setEMail($email);

        $emailAdicional = $form->get('eMailAdcional')->getData();
        $swCgm->setEMailAdcional($emailAdicional);

        $site = $form->get('site')->getData();
        $swCgm->setSite($site);

        return $this;
    }

    /**
     * Adiciona os campos de Atributos Dinamicos ao objeto SwCgm.
     *
     * @param SwCgm $swCgm
     *
     * @return $this
     */
    private function prePersistAtributoValores(SwCgm $swCgm, $isUpdate = false)
    {
        $form = $this->getForm();

        /** @var ModelManager $modelManager */
        $modelManager = $this->getModelManager();

        $entityManager = $modelManager->getEntityManager($this->getClass());

        $swAtributoCgmArray = $modelManager->findBy(SwAtributoCgm::class);

        /** @var SwAtributoCgm $swAtributoCgm */
        foreach ($swAtributoCgmArray as $swAtributoCgm) {
            $fieldName = self::ATTR_DINAMICO_FIELD_PREFIX . $swAtributoCgm->getCodAtributo();

            if ($form->has($fieldName)) {
                $fieldData = $form->get($fieldName)->getData();

                /** @var SwCgmAtributoValor $swCgmAtributoValor */
                $swCgmAtributoValor = $modelManager->findOneBy(SwCgmAtributoValor::class, [
                    'codAtributo' => $swAtributoCgm->getCodAtributo(),
                    'numcgm'      => $swCgm->getNumcgm()
                ]);

                if (is_null($swCgmAtributoValor)) {
                    $swCgmAtributoValor = (new SwCgmAtributoValor());
                }

                if (!is_null($fieldData)) {
                    $swCgmAtributoValor
                        ->setFkSwCgm($swCgm)
                        ->setFkSwAtributoCgm($swAtributoCgm)
                        ->setValor($fieldData);

                    if ($isUpdate) {
                        $entityManager->persist($swCgmAtributoValor);
                    } else {
                        $swCgm->addFkSwCgmAtributoValores($swCgmAtributoValor);
                    }
                }
            }
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->with('label.dados_endereco')
            ->add('fkSwCgm.fkSwPais', 'text', ['label' => 'label.pais'])
            ->add('fkSwCgm.fkSwMunicipio.fkSwUf.nomUf', 'text', ['label' => 'label.swBairro.codUf'])
            ->add('fkSwCgm.fkSwMunicipio.nomMunicipio', 'text', ['label' => 'label.cidade'])
            ->add('fkSwCgm.logradouro', null, ['label' => 'label.logradouro'])
            ->add('fkSwCgm.numero', null, ['label' => 'label.numero'])
            ->add('fkSwCgm.complemento', null, ['label' => 'label.complemento'])
            ->add('fkSwCgm.bairro', null, ['label' => 'label.servidor.bairro'])
            ->add('fkSwCgm.cep', null, ['label' => 'label.servidor.cep'])
            ->add('fkSwCgm.foneResidencial', null, ['label' => 'label.telefone_residencial'])
            ->add('fkSwCgm.foneComercial', null, ['label' => 'label.telefone_comercial'])
            ->add('fkSwCgm.foneCelular', null, ['label' => 'label.telefone_celular'])
            ->add('fkSwCgm.eMail', null, ['label' => 'label.usuario.email'])
            ->add('fkSwCgm.eMailAdcional', null, ['label' => 'label.email_adicional'])
            ->add('fkSwCgm.site', 'url', ['label' => 'label.site'])
            ->end()
            ->with('label.dados_endereco_correspondencia')
            ->add('fkSwCgm.fkSwPais1', 'text', ['label' => 'label.pais'])
            ->add('fkSwCgm.fkSwMunicipio1.fkSwUf.nomUf', 'text', ['label' => 'label.swBairro.codUf'])
            ->add('fkSwCgm.fkSwMunicipio1.nomMunicipio', 'text', ['label' => 'label.cidade'])
            ->add('fkSwCgm.logradouroCorresp', null, ['label' => 'label.logradouro'])
            ->add('fkSwCgm.numeroCorresp', null, ['label' => 'label.numero'])
            ->add('fkSwCgm.complementoCorresp', null, ['label' => 'label.complemento'])
            ->add('fkSwCgm.bairroCorresp', null, ['label' => 'label.servidor.bairro'])
            ->add('fkSwCgm.cepCorresp', null, ['label' => 'label.servidor.cep'])
            ->end();

        $showMapper->with('label.servidor.atributos');

        /** @var SwCgmAtributoValor $swCgmAtributoValor */
        foreach ($this->swCgm->getFkSwCgmAtributoValores() as $swCgmAtributoValor) {
            $fieldName = self::ATTR_DINAMICO_FIELD_PREFIX . $swCgmAtributoValor->getCodAtributo();
            $showMapper
                ->add($fieldName, 'text', [
                    'label'    => $swCgmAtributoValor->getFkSwAtributoCgm()->getNomAtributo(),
                    'template' => 'CoreBundle:Sonata/CRUD:show_custom_value.html.twig',
                    'data'     => $swCgmAtributoValor->getValor()
                ]);
        }

        $showMapper->end();
    }
}
