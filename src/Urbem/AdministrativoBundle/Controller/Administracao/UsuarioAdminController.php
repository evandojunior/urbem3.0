<?php

namespace Urbem\AdministrativoBundle\Controller\Administracao;

use Doctrine\ORM\EntityManager;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Urbem\CoreBundle\Model\Administracao\UsuarioModel;
use Urbem\CoreBundle\Entity\Administracao;

/**
 * Class UsuarioAdminController
 *
 * @package Urbem\AdministrativoBundle\Controller\Administracao
 */
class UsuarioAdminController extends CRUDController
{
    /**
     * Endpoint para campos autocomplete de usuarios
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function searchAction(Request $request)
    {
        $q = $request->get('q');

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getEntityManager();

        $usuarios = [];

        if (is_null($q) || empty($q)) {
            return new JsonResponse();
        }

        $usuarioModel = new UsuarioModel($em);
        $searchResults = $usuarioModel->search($q);

        /** @var Administracao\Usuario $usuario */
        foreach ($searchResults as $usuario) {
            array_push($usuarios, [
                'id' => $usuario->getNumcgm(),
                'label' => strtoupper($usuario->getFkSwCgm()->getNomCgm())
            ]);
        }

        $items = ['items' => $usuarios];

        return new JsonResponse($items);
    }
}
