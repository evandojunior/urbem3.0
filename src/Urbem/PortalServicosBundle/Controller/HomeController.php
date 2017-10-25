<?php

namespace Urbem\PortalServicosBundle\Controller;

use Urbem\CoreBundle\Controller\BaseController as Controller;

/**
 * Class HomeController
 *
 * @package Urbem\PortalServicosBundle\Controller
 */
class HomeController extends Controller
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $this->setBreadCrumb();

        return $this->render('PortalServicosBundle:Home:index.html.twig');
    }
}
