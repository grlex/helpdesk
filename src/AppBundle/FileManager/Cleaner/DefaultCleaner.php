<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 03.10.2017
 * Time: 21:03
 */

namespace AppBundle\FileManager\Cleaner;
use AppBundle\FileManager\FileManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use AppBundle\Entity\File;


class DefaultCleaner extends BaseCleaner implements  EventSubscriberInterface {
    protected $timeLimit;
    protected $now ;

    public function __construct($timeLimit = 1200){
        $this->timeLimit = intval($timeLimit);
    }


    public static function getSubscribedEvents(){
        return array(
            KernelEvents::TERMINATE => 'onKernelTerminate'
        );
    }
    public function onKernelTerminate(PostResponseEvent $event){
        $this->now = new \DateTime('now');
        $this->clean();
    }
    protected function filterFileToClean(File $file){

        return $this->now->getTimestamp() - $file->getCreated()->getTimestamp() > $this->timeLimit;
    }
} 