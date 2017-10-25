<?php

namespace Urbem\RecursosHumanosBundle\Resources\config\Sonata\FolhaPagamento;

use Doctrine\ORM\EntityManager;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\CoreBundle\Validator\ErrorElement;
use Symfony\Component\Config\Definition\Exception\Exception;
use Urbem\CoreBundle\Entity\Folhapagamento\ContratoServidorPeriodo;
use Urbem\CoreBundle\Entity\Folhapagamento\DecimoEvento;
use Urbem\CoreBundle\Entity\Folhapagamento\FeriasEvento;
use Urbem\CoreBundle\Entity\Folhapagamento\Fgts;
use Urbem\CoreBundle\Entity\Folhapagamento\LogErroCalculo;
use Urbem\CoreBundle\Entity\Folhapagamento\LogErroCalculoComplementar;
use Urbem\CoreBundle\Entity\Folhapagamento\LogErroCalculoDecimo;
use Urbem\CoreBundle\Entity\Folhapagamento\LogErroCalculoFerias;
use Urbem\CoreBundle\Entity\Folhapagamento\LogErroCalculoRescisao;
use Urbem\CoreBundle\Entity\Folhapagamento\PensaoEvento;
use Urbem\CoreBundle\Entity\Folhapagamento\PeriodoMovimentacao;
use Urbem\CoreBundle\Entity\Folhapagamento\PeriodoMovimentacaoSituacao;
use Urbem\CoreBundle\Entity\Folhapagamento\PrevidenciaPrevidencia;
use Urbem\CoreBundle\Entity\Folhapagamento\SalarioFamilia;
use Urbem\CoreBundle\Model\Administracao\ConfiguracaoModel;
use Urbem\CoreBundle\Model\Folhapagamento\FolhaComplementarModel;
use Urbem\CoreBundle\Model\Folhapagamento\LogErroCalculoModel;
use Urbem\CoreBundle\Model\Folhapagamento\PeriodoMovimentacaoModel;
use Urbem\CoreBundle\Model\Folhapagamento\RegistroEventoPeriodoModel;
use Urbem\CoreBundle\Model\Folhapagamento\TabelaIrrfModel;
use Urbem\CoreBundle\Model\Pessoal\ContratoModel;
use Urbem\CoreBundle\Resources\config\Sonata\AbstractSonataAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\HttpFoundation\RedirectResponse;

class PeriodoMovimentacaoAdmin extends AbstractSonataAdmin
{
    protected $baseRouteName = 'urbem_recursos_humanos_folha_pagamento_periodo_movimentacao';

    protected $baseRoutePattern = 'recursos-humanos/folha-pagamento/periodo-movimentacao';

    protected $customMessageDelete = 'ATENÇÃO! Ao confirmar a exclusão do período, todos os dados relativos à geração da folha de pagamento do período que está aberto serão perdidas!';

    /**
     * @param RouteCollection $collection
     */
    public function configureRoutes(RouteCollection $collection)
    {
        $collection
            ->clearExcept(array('create', 'list', 'delete'))
            ->add('abrir_periodo_movimentacao', 'abrir-periodo-movimentacao')
            ->add('monta_calcula_folha', 'monta-calcula-folha')
            ->add('deletar_informacoes_calculo', 'deletar-informacoes-calculo');
    }

    public function createQuery($context = 'list')
    {
        $query = parent::createQuery($context);
        /** @var EntityManager $em */
        $em = $this->modelManager->getEntityManager('CoreBundle:Folhapagamento\PeriodoMovimentacao');
        /** @var PeriodoMovimentacaoModel $periodoMovimentacaoModel */
        $periodoMovimentacaoModel = new PeriodoMovimentacaoModel($em);

        $periodoUnico = $periodoMovimentacaoModel->listPeriodoMovimentacao();
        $periodo = $periodoMovimentacaoModel->getOnePeriodo($periodoUnico);
        $codPeriodoMovimentacao = $periodo->getCodPeriodoMovimentacao();

        $query->andWhere(
            $query->expr()->eq('o.codPeriodoMovimentacao', ':param')
        );
        $query->setParameter('param', $codPeriodoMovimentacao);
        return $query;
    }

    /**
     * @param mixed $periodoMovimentacao
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function prePersist($periodoMovimentacao)
    {
        $container = $this->getConfigurationPool()->getContainer();
        $container->get('session')->getFlashBag()->clear();
        $container->get('session')->getFlashBag()->add('success', 'Um novo período foi aberto com sucesso');

        $this->forceRedirect('/recursos-humanos/folha-pagamento/rotina-mensal/');
    }

    /**
     * @param mixed $periodoMovimentacao
     */
    public function preRemove($periodoMovimentacao)
    {
        $container = $this->getConfigurationPool()->getContainer();
        try {
            $em = $this->modelManager->getEntityManager('Urbem\CoreBundle\Entity\Folhapagamento\PeriodoMovimentacao');

            $periodoMovimentacao = new PeriodoMovimentacaoModel($em);
            $periodoUnico = $periodoMovimentacao->listPeriodoMovimentacao();
            $periodoFinal = $periodoMovimentacao->getOnePeriodo($periodoUnico);
            //Validar configuracao se deve realizar o adiatamento do 13 no mes do aniversario do servidor
            //Gestão Recursos Humanos :: Folha de Pagamento :: Configuração :: Configurar Cálculo de 13º Salário
            $configuracaoModel = new ConfiguracaoModel($em);
            $boAdiantamenteMesAniversario = $configuracaoModel
                ->getConfiguracao('adiantamento_13_salario', ConfiguracaoModel::MODULO_RH_FOLHAPAGAMENTO, true);

            if ($boAdiantamenteMesAniversario == 'true') {
                $obErro = $periodoMovimentacao->cancelarAdiantamento13MesAniversario($this->getExercicio());
            }

            $periodo = $periodoMovimentacao->cancelarPeriodoMovimentacao('');

            $container->get('session')->getFlashBag()->add('success', 'O período atual foi fechado e todos os dados referentes ao período excluído foram removidos do sistema, Data Inicial: ' . $periodoFinal->getDtInicial()->format('d/m/Y') . ' Data Final: ' . $periodoFinal->getDtFinal()->format('d/m/Y'));

            $em->flush();
        } catch (Exception $e) {
            $container->get('session')->getFlashBag()->add('error', self::ERROR_REMOVE_DATA);
        }

        (new RedirectResponse($this->generateUrl('list')))->send();
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $id = $this->getAdminRequestId();

        $this->setBreadCrumb($id ? ['id' => $id] : []);

        $listMapper
            ->add('dtInicial', '', ['label' => 'Data Inicial', 'sortable' => false])
            ->add('dtFinal', '', ['label' => 'Data Final', 'sortable' => false])
            ->add('situacao', '', ['label' => 'Situação', 'mapped' => false])
            ->add('_action', 'actions', array(
                'actions' => array(
                    'delete' => array('template' => 'CoreBundle:Sonata/CRUD:list__action_delete.html.twig'),
                )
            ));
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $id = $this->getAdminRequestId();

        $this->setIncludeJs(array_merge(parent::getIncludeJs(), [
            '/recursoshumanos/javascripts/folhapagamento/periodoMovimentacao.js'
        ]));

        /** @var EntityManager $em */
        $em = $this->modelManager->getEntityManager('CoreBundle:Folhapagamento\PeriodoMovimentacao');
        $periodoMovimentacaoModel = new PeriodoMovimentacaoModel($em);

        $periodoUnico = $periodoMovimentacaoModel->listPeriodoMovimentacao();
        $periodo = $periodoMovimentacaoModel->getOnePeriodo($periodoUnico);
        $codPeriodoMovimentacao = $periodo->getCodPeriodoMovimentacao();

        if ($periodo) {
            $periodo = $periodo->getDtFinal()->modify('+1 day');
            $mes = $periodo->format('m');
            $ano = $periodo->format('Y');
        } else {
            $mes = date('m');
            $ano = date('Y');
            $periodo = new \DateTime();
        }

        /*
         *  PEGA O ULTIMO DIA DO MES
         */
        $lastDay = date("t", mktime(0, 0, 0, $mes, '01', $ano));
        $this->setBreadCrumb($id ? ['id' => $id] : []);

        if ($this->id($this->getSubject())) {
            (new RedirectResponse($this->generateUrl('list')))->send();
        }

        /** @var ContratoModel $contratoModel */
        $contratoModel = new ContratoModel($em);
        $contratos = [];
        $paramsBo["boAtivos"] = true;
        $paramsBo["boAposentados"] = false;
        $paramsBo["boRescindidos"] = false;
        $paramsBo["boPensionistas"] = true;
        $paramsBo["stTipoFolha"] = ContratoModel::TIPO_FOLHA_SALARIO;
        $contratosArray = $contratoModel->montaRecuperaContratosCalculoFolha(
            $paramsBo,
            $codPeriodoMovimentacao,
            '',
            [],
            [],
            []
        );

        foreach ($contratosArray as $contrato) {
            $contratos[] = $contrato['cod_contrato'];
        }
        $contratosStr = implode(",", $contratos);
        $formMapper
            ->add(
                'dtInicial',
                'sonata_type_date_picker',
                [
                    'format' => 'dd/MM/yyyy',
                    'label' => 'Data Inicial',
                    'attr' => [
                        'value' => $periodo->format('d/m/Y'),
                        'readonly' => 'readonly',
                    ],
                    'dp_icons' => '',
                ]
            )
            ->add(
                'dtFinal',
                'sonata_type_date_picker',
                [
                    'format' => 'dd/MM/yyyy',
                    'label' => 'Data Final',
                    'dp_default_date' => $periodo->format($lastDay . '/m/Y'),
                    'dp_min_date' => $periodo->format('d/m/Y'),
                    'dp_max_date' => $periodo->format($lastDay . '/m/Y'),
                    'dp_use_current' => false,
                ]
            )
            ->add('contratos', 'hidden', ['mapped' => false, 'data' => count($contratos)])
            ->add('contratosStr', 'hidden', ['mapped' => false, 'data' => $contratosStr]);
    }
}
