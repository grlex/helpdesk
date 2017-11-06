<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 18.09.2017
 * Time: 17:30
 */

namespace AppBundle\FOSComment;


use AppBundle\FileManager\FileManager;
use AppBundle\Service\HtmlImagesExtractor;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Collections\ArrayCollection;
use FOS\CommentBundle\Event\CommentEvent;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use FOS\CommentBundle\Events;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RequestStack;
use AppBundle\Entity\File;

class EventSubscriber implements EventSubscriberInterface {


    private $doctrine;
    private $imagesExtractor;
    public function __construct(Registry $doctrine, HtmlImagesExtractor $imagesExtractor){
        $this->doctrine = $doctrine;
        $this->imagesExtractor = $imagesExtractor;
    }
    public static function getSubscribedEvents()
    {
        return array(
            Events::COMMENT_PRE_PERSIST => array(
                'onPrePersist'
            ),
            ExtraEvents::COMMENT_PRE_REMOVE=> array(
                'onPreRemove'
            )
        );
    }
    public function onPrePersist(CommentEvent $event){
        $em = $this->doctrine->getEntitymanager();
        $comment = $event->getComment();
        $oldImages = [];
        if($comment->getId()) {
            $originalCommentData = $em->getUnitOfWork()->getOriginalEntityData($comment);
            $oldImages = $this->imagesExtractor->extract($originalCommentData['body'] ?: '');
        }
        $newImages = $this->imagesExtractor->extract($comment->getBody());

        foreach($newImages as $newImage){
            $newImage->setConfirmed(1);
            $em->persist($newImage);
            foreach($oldImages as $key=>$oldImage){
                if ($oldImage->getId() == $newImage->getId()) {
                    unset($oldImages[$key]);
                    break;
                }
            }
        }
        foreach($oldImages as $oldImage) {
            $em->remove($oldImage);
        }

    }
    public function onPreRemove(CommentEvent $event){
        $em = $this->doctrine->getEntityManager();
        $bodyImages = $this->imagesExtractor->extract($event->getComment()->getBody());
        foreach($bodyImages as $image) $em->remove($image);
        //$em->flush();
    }

}