<?php

namespace Urbem\CoreBundle\Handler;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Urbem\CoreBundle\Entity\Administracao\Usuario;

/**
 * Class LoginSuccessHandler
 */
class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    protected $router;
    protected $storage;
    protected $autorization;

    public function __construct(Router $router, AuthorizationChecker $autorization)
    {
        $this->router = $router;
        $this->autorization = $autorization;
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        $response = new RedirectResponse($this->router->generate('home-urbem'));

        if ($this->autorization->isGranted('ROLE_MUNICIPE')) {
            $response = new RedirectResponse($this->router->generate('home-portalservicos'));
        }

        return $response;
    }
}