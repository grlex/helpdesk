<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 30.09.2017
 * Time: 18:58
 */

namespace AppBundle\FileManager\Storage;


use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class GeneralStorage extends BaseStorage{

    public function supportsExtension($extension){
        return true;
    }
    public function save( File $file, $uniqueValue=null){
        $baseName = $this->generateName($file, $uniqueValue);
        $file->move($this->path, $baseName);
        return $baseName;
    }
    public function get( $baseName, $prefix=''){
        return new File(sprintf('%s/%s',$this->path, $baseName));
    }
    public function getPrefixes(){
        return [];
    }
    protected function getPath( $name){
        return $this->path.'/'.$name;
    }


} 