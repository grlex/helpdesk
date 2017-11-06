<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 04.10.2017
 * Time: 13:16
 */

namespace AppBundle\FileManager\Cleaner;

use AppBundle\Entity\File;
use Doctrine\Common\Collections\Collection;
use AppBundle\FileManager\FileManager;

abstract class BaseCleaner implements CleanerInterface {

    protected $fileManager;

    public function getFileManager(){
        return $this->fileManager;
    }
    public function setFileManager(FileManager $fileManager){
        $this->fileManager = $fileManager;
    }

    public function clean(){
        if(!$this->fileManager) return;

        $files = $this->fileManager->getFileMetas(FileManager::STATUS_UNCONFIRMED);
        $files = array_filter($files, [ $this, 'filterFileToClean' ] );
        $this->fileManager->deleteFiles($files);

    }

    protected abstract function filterFileToClean(File $file);
} 