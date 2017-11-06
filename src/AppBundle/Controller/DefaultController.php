<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        return $this->redirect('/request/my-list');
    }
    /**
     * @Route("/change-locale/{_locale}", requirements={"_locale":"ru|en"})
     * @Method({"GET"})
     */
    public function changeLocale(Request $request){
        $backUri = $request->query->get('back-uri', '/');
        return $this->redirect($backUri);
    }
}
