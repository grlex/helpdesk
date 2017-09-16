<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
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
        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
        ]);
    }
    /**
     * @Route("/secured")
     * @Security("has_role('ROLE_USER')")
     */
    public function securedAction(){
        /*$checker = $this->get('security.authorization_checker');
        $isFully = $checker->isGranted('IS_AUTHENTICATED_FULLY') ? 'yes' : 'no';
        $isRemembered = $checker->isGranted('IS_AUTHENTICATED_REMEMBERED') ? 'yes' : 'no';
        $isAnonymously = $checker->isGranted('IS_AUTHENTICATED_ANONYMOUSLY') ? 'yes' : 'no';
        printf('Authenticated fully: %s',$isFully);
        printf('Authenticated remembered: %s',$isRemembered);
        printf('Authenticated anonymously: %s',$isAnonymously);*/
        return new Response('Access granted!');
    }
}
