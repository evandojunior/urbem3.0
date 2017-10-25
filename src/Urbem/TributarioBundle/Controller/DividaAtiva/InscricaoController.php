<?php

namespace Urbem\TributarioBundle\Controller\DividaAtiva;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Urbem\CoreBundle\Controller\BaseController;
use Urbem\CoreBundle\Model\Tributaria\DividaAtiva\Inscricao\InscricaoModel;

class InscricaoController extends BaseController
{
    protected $baseRouteName = 'urbem_tributario_divida_ativa_inscricao_cancelada';

    /**
     * Home
     */
    public function homeAction()
    {
        $this->setBreadCrumb();
        return $this->render('TributarioBundle::DividaAtiva/Inscricao/home.html.twig');
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function buscarSwAssuntoAction(Request $request)
    {
        $id = $request->attributes->get('_id');
        $assuntos = new InscricaoModel($this->getDoctrine()->getManager(), $this->getExercicio());
        $response = new Response();
        $response->setContent(json_encode(['data' => $assuntos->findSwAssunto($id)]));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function buscarProcessoAction(Request $request)
    {
        $id = $request->attributes->get('_id');
        $assuntos = new InscricaoModel($this->getDoctrine()->getManager(), $this->getExercicio());
        $response = new Response();
        $response->setContent(json_encode(['data' => $assuntos->findProcessos($id)]));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function registrosAction(Request $request)
    {
        $id = $request->attributes->get('_id');
        $this->setBreadCrumb($id ? ['_id' => $id] : []);
        $explodeInscricao = explode('-', $id);

        $model = new InscricaoModel($this->getDoctrine()->getManager(), $this->getExercicio());
        $dividaCancelada = $model->findByOneDividaCancelada($explodeInscricao[0], $explodeInscricao[1]);

        if (empty($dividaCancelada)) {
            $this->redirectToRoute(sprintf('%s_%s', $this->baseRouteName, 'create'));
        }
        $dividaParcelamento = $model->findDividaParcelamento($dividaCancelada->getCodInscricao(), $dividaCancelada->getExercicio());
        $registros = $model->findRegistros($dividaParcelamento['numParcelamento'], $explodeInscricao[0], $explodeInscricao[1]);

        $admin = [
            'request' => $request,
            'registros' => $registros
        ];

        return $this->render(
            'TributarioBundle::DividaAtiva/Inscricao/cancelar/list_registros.html.twig',
            [
                'admin' => $admin
            ]
        );
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function emitirDocumentoAction(Request $request)
    {
        $id = $request->attributes->get('_id');
        $explodeInscricao = explode('-', $id);

        $model = new InscricaoModel($this->getDoctrine()->getManager(), $this->getExercicio());
        $dividaCancelada = $model->findByOneDividaCancelada(trim($explodeInscricao[0]), trim($explodeInscricao[1]));
        $tipoDoc = str_replace('.agt', '', trim($explodeInscricao[2]));
        $emissaoDocumento = $model->buildEmissaoDocumento($dividaCancelada, $tipoDoc);
        $message = $this->get('translator')->transChoice('label.dividaAtivaInscricao.message.emissaoRealizada', 0, [], 'messages');

        if (!empty($emissaoDocumento)) {
            $ifEmissaoDocumento = $model->findEmissaoDocumento(
                $emissaoDocumento->getNumParcelamento(),
                $emissaoDocumento->getNumEmissao(),
                $emissaoDocumento->getCodTipoDocumento(),
                $emissaoDocumento->getCodDocumento(),
                $emissaoDocumento->getNumDocumento(),
                $emissaoDocumento->getExercicio()
            );

            if (!empty($ifEmissaoDocumento)) {
                $this->addFlash('error', $message);
                return $this->redirectToRoute('tributario_divida_ativa_inscricao_cancelar_registros', ['_id' => sprintf('%s-%s', trim($explodeInscricao[0]), trim($explodeInscricao[1]))]);
            }

            $model->save($emissaoDocumento);
            $dados = null;
            $renderViewCustom = null;
            switch ($tipoDoc) {
                case $model::NOTIFICACAO_DIVIDA:
                    $dados = $model->findDadosDocumentoNotificacaoDa($emissaoDocumento->getNumParcelamento());
                    $renderViewCustom = $this->renderViewCustom($model->dadosNotificacaoDa($dados), $model::NOTIFICACAO_DIVIDA_FILE);
                    break;
                case $model::TERMO_CONSOLIDACAO:
                    $dados = $model->findDadosTermoConsolidacaoDa($emissaoDocumento->getNumParcelamento(), $emissaoDocumento->getCodTipoDocumento());
                    $renderViewCustom = $this->renderViewCustom($model->dadosTermoConsolidacao($dados), $model::TERMO_CONSOLIDACAO_FILE);
                    break;
            }

            if (!empty($renderViewCustom)) {
                return new Response(
                    $this->get('knp_snappy.pdf')
                        ->getOutputFromHtml(
                            $renderViewCustom['renderView'],
                            array(
                                'encoding' => 'utf-8',
                                'enable-javascript' => true,
                                'footer-line' => true,
                                'footer-left' => 'URBEM - CNM',
                                'footer-right' => '[page]',
                                'footer-center' => 'www.cnm.org.br',
                            )
                        ),
                    200,
                    array(
                        'Content-Type'        => 'application/pdf',
                        'Content-Disposition' => sprintf('attachment; filename="%s"', $renderViewCustom['filename'])
                    )
                );
            } else {
                return $this->redirectToRoute('tributario_divida_ativa_inscricao_cancelar_registros', ['_id' => sprintf('%s-%s', trim($explodeInscricao[0]), trim($explodeInscricao[1]))]);
            }
        } else {
            $this->addFlash('error', $message);
            return $this->redirectToRoute('tributario_divida_ativa_inscricao_cancelar_registros', ['_id' => sprintf('%s-%s', trim($explodeInscricao[0]), trim($explodeInscricao[1]))]);
        }
    }

    /**
     * @param $dadosPdf
     * @param $filename
     * @return array|null
     */
    public function renderViewCustom($dadosPdf, $filename)
    {
        if (!empty($dadosPdf)) {
            $emissaoDocumento = new \DateTime('now');
            return [
                'filename' => sprintf('%s%s%s', $filename, $emissaoDocumento->format("Ymd_His"), '.pdf'),
                'renderView' => $this->renderView(
                    'TributarioBundle:DividaAtiva/Inscricao/cancelar:' . $filename . '.html.twig',
                    $dadosPdf
                )
            ];
        }
        return null;
    }
}
