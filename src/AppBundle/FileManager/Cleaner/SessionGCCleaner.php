<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 02.10.2017
 * Time: 18:31
 */

namespace AppBundle\FileManager\Cleaner;


use AppBundle\Entity\File;
use AppBundle\FileManager\FileManager;

use AppBundle\Session\SessionGCEvent;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;


class SessionGCCleaner extends BaseCleaner implements  EventSubscriberInterface {

    protected $now, $timeLimit;
    private $delayedEvent;
    public static function getSubscribedEvents(){
        return array(
            'session.gc'=>'onSessionGC',
            KernelEvents::TERMINATE => 'onKernelTerminate'
        );
    }


    public function onSessionGC(SessionGCEvent $event){
        $this->delayedEvent = $event;
    }
    public function onKernelTerminate(){

        if($this->delayedEvent) {
            $this->now = new \DateTime('now');
            $this->timeLimit = $this->delayedEvent->getLifetime();
            $this->clean();
        }
    }
    protected function filterFileToClean(File $file){
        return $this->now->getTimestamp() - $file->getCreated()->getTimestamp() > $this->timeLimit;
    }


} 