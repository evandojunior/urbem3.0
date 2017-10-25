<?php

namespace Urbem\FinanceiroBundle\Controller\Empenho;

use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ReemitirEmpenhoAdminController extends Controller
{
    public function load(array $configs, ContainerBuilder $container)
    {
        parent::load($configs, $container);
    }
    
    public function reemitirEmpenhoAction(Request $request)
    {
        $codPreEmpenho = $request->query->get('codPreEmpenho');
        $exercicio = $request->query->get('exercicio');
        
        $entityManager = $this->getDoctrine()->getManager();
        
        $currentUser = $this->get('security.token_storage')->getToken()->getUser();
        
        $dadosEmpenho = (new \Urbem\CoreBundle\Model\Empenho\PreEmpenhoModel($entityManager))
        ->getDadosEmpenho($codPreEmpenho, $exercicio);

        $emissaoEmpenho= array();
        $emissaoEmpenho['dtEmpenho'] = $dadosEmpenho['dtEmpenho'];
        $emissaoEmpenho['hora'] = $dadosEmpenho['hora'];

        $html = $this->renderView(
            'FinanceiroBundle:Empenho/Empenho:reemitirEmpenho.html.twig',
            array(
                'logoTipo' => $this->container->get('urbem.configuracao')->getLogoTipo(),
                'dadosEmpenho' => $dadosEmpenho,
                'usuario' => $currentUser,
                'entidade' => $this->get('urbem.entidade')->getEntidade(),
                'modulo' => 'Empenho',
                'subModulo' => 'Empenho',
                'funcao' => 'Nota de Empenho',
                'dtEmissao' => new \DateTime(),
                'nomRelatorio' => 'Empenho N. ' . $dadosEmpenho['empenho'] . ' REEMISSÃO',
                'versao' => '3.0.0',
                'emissaoDocumento' => $emissaoEmpenho
            )
        );
        
        $filename = "NotaDeEmpenho_" . (new \DateTime())->format("Ymd_His") . ".pdf";
        
        return new Response(
            $this->get('knp_snappy.pdf')
            ->getOutputFromHtml(
                $html,
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
                'Content-Disposition' => sprintf('attachment; filename="%s"', $filename)
            )
        );
    }
}
