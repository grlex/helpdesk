<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 24.10.2017
 * Time: 18:28
 */

namespace AppBundle\Service;


use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Collections\ArrayCollection;

class HtmlImagesExtractor {
    private $doctrine;
    private $fileIdAttribute;
    public function __construct(Registry $doctrine, $fileIdAttribute='data-file-id'){
        $this->doctrine = $doctrine;
        $this->fileIdAttribute = $fileIdAttribute;
    }
    public function extract($html){
        if(empty($html)) return [];
        $dom = new \DOMDocument();
        @$dom->loadHTML($html); // rawBody is empty yet
        $images = $dom->getElementsByTagName('img');
        $fileIds = [];
        foreach($images as $image){
            if($image->hasAttribute($this->fileIdAttribute)){
                $fileIds[] = $image->getAttribute($this->fileIdAttribute);
            }
        }

        $files = empty($fileIds) ? []
            : $this->doctrine->getManager()
                ->createQuery(sprintf('select f from AppBundle\Entity\File f where f.id in ( %s )',join(',',$fileIds)))
                ->getResult();


        return $files;
    }
} 