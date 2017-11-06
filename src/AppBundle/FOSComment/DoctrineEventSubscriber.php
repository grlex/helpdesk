<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 25.10.2017
 * Time: 11:57
 */

namespace AppBundle\FOSComment;


use AppBundle\Entity\Comment;
use Doctrine\Common\EventSubscriber as DEventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use FOS\CommentBundle\Event\CommentEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class DoctrineEventSubscriber implements DEventSubscriber{

    private $dispatcher;
    public function __construct(EventDispatcherInterface $dispatcher){
        $this->dispatcher = $dispatcher;
    }
    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array(
            'preRemove'
        );
    }
    public function preRemove(LifecycleEventArgs $args){

        if(!($args->getObject() instanceof Comment)) return;

        $this->dispatcher->dispatch(ExtraEvents::COMMENT_PRE_REMOVE,
            new CommentEvent($args->getObject()));
    }
}