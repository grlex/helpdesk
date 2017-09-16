<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 13.09.2017
 * Time: 10:17
 */

namespace AppBundle\Service;


use AppBundle\Entity\User;
use AppBundle\Form\LoginType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\AuthenticationEvents;
use Symfony\Component\Security\Core\Event\AuthenticationEvent;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Form\FormFactory;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;


class UserRemovingSubscriber implements EventSubscriberInterface {

    private $doctrine;
    private $user;

    public function __construct(Registry $doctrine,
                                TokenStorageInterface $tokenStorage
    ){
        $this->doctrine = $doctrine;
        $this->user = $tokenStorage->getToken() ? $tokenStorage->getToken()->getUser() : null;
    }

    public static function getSubscribedEvents()
    {
        return array(
            AuthenticationEvents::AUTHENTICATION_SUCCESS=>array(
                'onAuthenticationSuccess'
            ),
            KernelEvents::CONTROLLER=>array('onKernelController'
            )
        );

    }
    public function onAuthenticationSuccess(AuthenticationEvent $event){
        //dump('auth');
        //$this->user = $event->getAuthenticationToken()->getUser();
    }
    public function onKernelController(FilterControllerEvent $event){
        $user = $this->user;
        if(!($user instanceof User)) return;
        if($user->isRemoved()){

            $em = $this->doctrine->getManager();
            $em->remove($user);
            $em->flush();

            $response = new RedirectResponse(sprintf('/account/login-removed/%s',$user->getLogin()));
            $response->send();
            die();
        }
    }


}