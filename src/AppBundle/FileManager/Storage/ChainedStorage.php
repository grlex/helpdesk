<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 01.10.2017
 * Time: 19:28
 */

namespace AppBundle\FileManager\Storage;


use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Intl\Exception\NotImplementedException;

class ChainedStorage implements StorageInterface {

    protected $storageList;
    public function __construct(){
        $this->storageList = array();
    }


    public function supportsExtension($extension)
    {
        foreach($this->storageList as $storage){
            if($storage->supportsExtension($extension)){
                return true;
            }
        }
        return false;
    }

    public function save(File $file, $uniqueValue = null)
    {
        return $this->getStorage('tmp.'.$file->guessExtension())->save($file,$uniqueValue);
    }

    public function delete($name)
    {
        $this->getStorage($name)->delete($name);
    }

    public function get($name, $prefix = '')
    {
        return $this->getStorage($name)->get($name, $prefix);
    }

    public function getPrefixes()
    {
        $prefixes = [];
        foreach($this->storageList as $storage)
            $prefixes = array_merge($prefixes, $storage->getPrefixes());
        return $prefixes;
    }

    // ======================== ===========


    public function addStorage($name,  StorageInterface $storage){
        $this->storageList[$name] = $storage;
    }

    public function removeStorage($name){
        if(array_key_exists($name,$this->storageList));
        unset($this->storageList[$name]);
    }

    private  function getStorage($filename){
        $extension = $this->extractExtension($filename);
        foreach($this->storageList as $storage){
            if($storage->supportsExtension($extension)){
                return $storage;
            }
        }
    }
    protected  function extractExtension($filename){
        return substr(strrchr($filename, '.'),1);
    }
}