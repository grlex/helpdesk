<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 01.10.2017
 * Time: 18:55
 */

namespace AppBundle\FileManager\Storage;


use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

abstract class BaseStorage implements StorageInterface {
    protected $path;
    private static $random;
    public function __construct($path){
        $this->path = $path;
    }
    public function delete($baseName){
        $prefixes = $this->getPrefixes();
        if(empty($prefixes)) $prefixes = [''];
        foreach($prefixes as $prefix){
            $path = sprintf('%s/%s%s',$this->path, empty($prefix) ? '' : $prefix.'.', $baseName);
            if(file_exists($path)) unlink($path);
        }
    }

    protected function generateName(UploadedFile $file, $uniqueValue=null){
        $uniqueValue = $uniqueValue ?: uniqid($this->getRandom());
        $extension = $file->guessExtension() ?: $file->getClientOriginalExtension();
        return sprintf('%s.%s', $uniqueValue, $extension);
    }
    public abstract function getPrefixes();

    private  function getRandom(){
        if(!self::$random) self::$random=\mt_rand(0,1000);
        return self::$random++;
    }


}
