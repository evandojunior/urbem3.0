<?php

namespace Urbem\TributarioBundle\Resources\config\Sonata\Arrecadacao;

use Doctrine\ORM\EntityRepository;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\CoreBundle\Validator\ErrorElement;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Urbem\CoreBundle\Entity\Arrecadacao\Documento;
use Urbem\CoreBundle\Entity\Arrecadacao\DocumentoCgm;
use Urbem\CoreBundle\Entity\Arrecadacao\DocumentoEmpresa;
use Urbem\CoreBundle\Entity\Arrecadacao\DocumentoImovel;
use Urbem\CoreBundle\Entity\Imobiliario\Lote;
use Urbem\CoreBundle\Entity\Imobiliario\Imovel;
use Urbem\CoreBundle\Entity\Imobiliario\Localizacao;
use Urbem\CoreBundle\Entity\SwCgm;
use Urbem\CoreBundle\Model\Arrecadacao\DocumentoEmissaoModel;
use Urbem\CoreBundle\Resources\config\Sonata\AbstractSonataAdmin;

class DocumentoEmissaoAdmin extends AbstractSonataAdmin
{
    protected $baseRouteName = 'urbem_tributario_arrecadacao_documento_emissao';
    protected $baseRoutePattern = 'tributario/arrecadacao/documento-emissao';
    protected $includeJs = array(
        '/tributario/javascripts/arrecadacao/documento-emissao.js'
    );

    /**
     * @param RouteCollection $collection
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->add('download', 'download/' . $this->getRouterIdParameter());
        $collection->add('geracertidao', 'gera_certidacao/');
    }

    /**
     * @return string
     */
    public function getGoBackURL()
    {
        return '/tributario/arrecadacao/documento-emissao/list';
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $id = $this->getAdminRequestId();
        $this->setBreadCrumb($id ? ['id' => $id] : []);

        $fieldOptions['fkArrecadacaoDocumento'] = array(
            'label' => 'label.arrecadacaoDocumentoEmissao.codDocumento',
            'class' => Documento::class,
            'mapped' => true,
            'required' => true,
            'placeholder' => 'Selecione',
            'attr' => array(
                'class' => 'select2-parameters '
            )
        );

        $fieldOptions['fkArrecadacaoDocumentoCgns'] = [
            'label' => 'label.arrecadacaoDocumentoEmissao.cgm',
            'class' => SwCgm::class,
            'json_from_admin_code' => $this->code,
            'json_query_builder' => function (EntityRepository $er, $term, Request $request) {
                $qb = $er->createQueryBuilder('o');
                if (is_numeric($term)) {
                    $qb->where('o.numcgm = :numcgm');
                    $qb->setParameter('numcgm', $term);
                } else {
                    $qb->where('LOWER(o.nomCgm) LIKE :nomCgm');
                    $qb->setParameter('nomCgm', '%' . strtolower($term) . '%');
                }
                return $qb;
            },
            'mapped' => false,
            'required' => false,
        ];

        $fieldOptions['inscricaoEconomica'] = [
            'label' => 'label.economico.licenca.inscricaoEconomica',
            'required' => false,
            'mapped' => false,
            'route' => [
                'name' => 'get_sw_cgm_inscricao_economica'
            ],
        ];

        $fieldOptions['fkImobiliarioLocalizacao'] = [
            'label' => 'label.imobiliarioCondominio.localizacao',
            'class' => Localizacao::class,
            'json_from_admin_code' => $this->code,
            'json_query_builder' => function (EntityRepository $er, $term) {
                $qb = $er->createQueryBuilder('o');
                $qb->andWhere('o.codigoComposto LIKE :codigoComposto');
                $qb->setParameter('codigoComposto', sprintf('%%%s%%', strtolower($term)));
                $qb->orderBy('o.codLocalizacao', 'ASC');
                return $qb;
            },
            'mapped' => false,
            'required' => false,
        ];

        $fieldOptions['fkImobiliarioLote'] = array(
            'label' => 'label.imobiliarioCondominio.lote',
            'class' => Lote::class,
            'req_params' => [
                'codLocalizacao' => 'varJsCodLocalizacao'
            ],
            'json_from_admin_code' => $this->code,
            'json_query_builder' => function (EntityRepository $er, $term, Request $request) {
                $qb = $er->createQueryBuilder('o');
                $qb->innerJoin('o.fkImobiliarioLoteLocalizacao', 'l');
                if ($request->get('codLocalizacao') != '') {
                    $qb->andWhere('l.codLocalizacao = :codLocalizacao');
                    $qb->setParameter('codLocalizacao', $request->get('codLocalizacao'));
                }
                $qb->andWhere('lpad(upper(l.valor), 10, \'0\') = :valor');
                $qb->setParameter('valor', str_pad($term, 10, '0', STR_PAD_LEFT));

                $qb->leftJoin('o.fkImobiliarioImovelLotes', 'i');
                $qb->andWhere('i.inscricaoMunicipal is not null');
                $qb->orderBy('o.codLote', 'ASC');
                return $qb;
            },
            'mapped' => false,
            'required' => false,
        );

        $fieldOptions['fkImobiliarioImovel'] = array(
            'label' => 'label.imobiliarioImovel.inscricaoImobiliaria',
            'class' => Imovel::class,
            'req_params' => [
                'codLocalizacao' => 'varJsCodLocalizacao',
                'codLote' => 'varJsCodLote'
            ],
            'json_from_admin_code' => $this->code,
            'json_query_builder' => function (EntityRepository $er, $term, Request $request) {
                $qb = $er->createQueryBuilder('o');
                $qb->innerJoin('o.fkImobiliarioImovelConfrontacao', 'ic');
                if ($request->get('codLocalizacao') != '') {
                    $qb->innerJoin('ic.fkImobiliarioConfrontacaoTrecho', 't');
                    $qb->innerJoin('t.fkImobiliarioConfrontacao', 'c');
                    $qb->innerJoin('c.fkImobiliarioLote', 'l');
                    $qb->innerJoin('l.fkImobiliarioLoteLocalizacao', 'll');
                    $qb->andWhere('ll.codLocalizacao = :codLocalizacao');
                    $qb->setParameter('codLocalizacao', $request->get('codLocalizacao'));
                }
                if ($request->get('codLote') != '') {
                    $qb->andWhere('ic.codLote = :codLote');
                    $qb->setParameter('codLote', $request->get('codLote'));
                }
                $qb->andWhere('o.inscricaoMunicipal = :inscricaoMunicipal');
                $qb->setParameter('inscricaoMunicipal', $term);
                $qb->orderBy('o.inscricaoMunicipal', 'ASC');
                return $qb;
            },
            'mapped' => false,
            'required' => false,
        );

        $formMapper
            ->with('label.arrecadacaoDocumentoEmissao.dados')
            ->add('fkArrecadacaoDocumento', 'entity', $fieldOptions['fkArrecadacaoDocumento'])
            ->add('fkArrecadacaoDocumentoCgns', 'autocomplete', $fieldOptions['fkArrecadacaoDocumentoCgns'])
            ->add('inscricaoEconomica', 'autocomplete', $fieldOptions['inscricaoEconomica'])
            ->end()
            ->with('label.imobiliarioImovel.inscricaoImobiliaria')
            ->add('fkImobiliarioLocalizacao', 'autocomplete', $fieldOptions['fkImobiliarioLocalizacao'])
            ->add('fkImobiliarioLote', 'autocomplete', $fieldOptions['fkImobiliarioLote'])
            ->add('fkImobiliarioImovel', 'autocomplete', $fieldOptions['fkImobiliarioImovel']);
    }

    /**
     * @param ErrorElement $errorElement
     * @param mixed $object
     * @return bool
     */
    public function validate(ErrorElement $errorElement, $object)
    {
        $imovel = $this->getForm()
            ->get('fkImobiliarioImovel')->getData();
        $cgn = $this->getForm()
            ->get('fkArrecadacaoDocumentoCgns')->getData();
        $inscricaoEconomica = $this->getForm()
            ->get('inscricaoEconomica')->getData();

        if ((!$imovel) && (!$cgn) && (!$inscricaoEconomica)) {
            $error = $this->getTranslator()->trans('label.arrecadacaoDocumentoEmissao.erroValidacao');

            $errorElement->with('fkImobiliarioImovel')->addViolation($error)->end();
            $errorElement->with('fkArrecadacaoDocumentoCgns')->addViolation($error)->end();
            $errorElement->with('inscricaoEconomica')->addViolation($error)->end();
        }
    }

    /**
     * @param mixed $object
     * @throws \Exception
     */
    public function prePersist($object)
    {
        $container = $this->getConfigurationPool()->getContainer();

        try {
            $em = $this->modelManager->getEntityManager($this->getClass());

            $documentoEmisssaoModel = new DocumentoEmissaoModel($em);

            $params = array(
                'cod_documento' => $object->getCodDocumento(),
                'exercicio' => $this->getExercicio()
            );

            $numDocumento = $documentoEmisssaoModel->getNextVal($params);

            $object->setExercicio($this->getExercicio());
            $object->setNumDocumento($numDocumento);
            $object->setNumCgm($this->getCurrentUser()->getNumcgm());

            if ($this->getForm()->get('fkArrecadacaoDocumentoCgns')->getData()) {
                $cgm = $this->getForm()->get('fkArrecadacaoDocumentoCgns')->getData();

                $documentoCgns = new DocumentoCgm();

                $documentoCgns->setNumcgm($cgm->getNumcgm());
                $documentoCgns->setFkArrecadacaoDocumentoEmissao($object);

                $object->addFkArrecadacaoDocumentoCgns($documentoCgns);
            }

            if ($this->getForm()->get('fkImobiliarioImovel')->getData()) {
                $documentoImovel = new DocumentoImovel();
                $documentoImovel->setInscricaoMunicipal($this->getForm()->get('fkImobiliarioImovel')->getData('inscricaoMunicipal'));
                $documentoImovel->setFkArrecadacaoDocumentoEmissao($object);
                $object->addFkArrecadacaoDocumentoImoveis($documentoImovel);
            }

            if ($this->getForm()->get('inscricaoEconomica')->getData()) {
                $documentoEmpresa = new DocumentoEmpresa();
                $documentoEmpresa->setInscricaoEconomica($this->getForm()->get('inscricaoEconomica')->getData());
                $documentoEmpresa->setFkArrecadacaoDocumentoEmissao($object);
                $object->addFkArrecadacaoDocumentoEmpresas($documentoEmpresa);
            }

            $em->persist($object);
            $em->flush();
            $this->redirectCreate($object);
        } catch (\Exception $e) {
            $container->get('session')->getFlashBag()->add('error', $this->getTranslator()->trans('contactSupport'));
            $container->get('session')->getFlashBag()->add('error', $e->getMessage());
            throw $e;
        }
    }

    /**
     * @param $object
     */
    public function redirectCreate($object)
    {
        $message = $this->getTranslator()->trans('label.arrecadacaoDocumentoEmissao.msgSucesso');
        $message.= '(Certidão:' . $object->getNumDocumento() . ')';

        $container = $this->getConfigurationPool()->getContainer();
        $container->get('session')->getFlashBag()->add('success', $message);
        $this->forceRedirect(sprintf('/tributario/arrecadacao/documento-emissao/download/%d~%s', $object->getNumDocumento(), $object->getExercicio()));
    }

    /**
     * @param mixed $object
     * @return string
     */
    public function toString($object)
    {
        return $object instanceof CarneDevolucao
            ? $object->getNumeracao()
            : $this->getTranslator()->trans('label.arrecadacaoCarneDevolucao.modulo');
    }
}
