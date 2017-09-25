<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 18.09.2017
 * Time: 17:30
 */

namespace AppBundle\Service;


use FOS\CommentBundle\Event\CommentEvent;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use FOS\CommentBundle\Events;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RequestStack;

class FOSCommentSubscriber implements EventSubscriberInterface {

    private $request;
    private $container;
    public function __construct(RequestStack $requestStack, ContainerInterface $container){
        $this->request = $requestStack->getCurrentRequest();
        $this->container = $container;
    }
    public static function getSubscribedEvents()
    {
        return array(
            Events::COMMENT_POST_PERSIST => array(
                'onPostPersist'
            )
        );
    }
    public function onPostPersist(CommentEvent $event){


        $comment = $event->getComment();
        $files = $this->request->files->all();
        $basePath = sprintf('%s/web/files',$this->container->getParameter('kernel.project_dir'));

        foreach($files as $name=>$file){
            $fileName = sprintf('c%df%s',$comment->getId(),$name);
            //$file = new UploadedFile('','');
            $file->move($basePath, $fileName.'.jpg');
        }

    }

}