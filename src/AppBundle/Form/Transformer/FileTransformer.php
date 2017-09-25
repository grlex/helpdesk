<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 21.09.2017
 * Time: 18:11
 */

namespace AppBundle\Form\Transformer;

use AppBundle\Entity\File as FileEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Doctrine\Bundle\DoctrineBundle\Registry;
use AppBundle\Service\FileStorage;

class FileTransformer implements DataTransformerInterface {


    private $doctrine;


    public function __construct( Registry $doctrine){
        $this->doctrine = $doctrine;

    }
    public function transform($entity)
    {
        if($entity===null) return null;

        $file = array(
            'id'=> $entity->getId(),
            'name'=>$entity->getOriginalName(),
        );
        return $file;
    }


    public function reverseTransform($file)
    {

        $id = $file['id'];
        $entity = $this->doctrine->getRepository(FileEntity::class)->find($id);
        return $entity;
    }
}