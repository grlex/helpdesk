<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 05.09.2017
 * Time: 17:53
 */

namespace AppBundle\Controller;


use AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Form\LoginType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class AccountController extends Controller {

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/account/login")
     */
    public function loginAction(Request $request, AuthenticationUtils $authUtils){

        // get the login error if there is one
        $error = $authUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authUtils->getLastUsername();

        $form = $this->createForm(LoginType::class, ['login'=>$lastUsername]);


        if($error) {
            $errorMessage = $this->get('translator')->trans($error->getMessage(),array(),'forms');
            $form->addError(new FormError($errorMessage));
        }
        return $this->render('account/login.html.twig', array(
            'form'=>$form->createView(),
        ));
    }

} 