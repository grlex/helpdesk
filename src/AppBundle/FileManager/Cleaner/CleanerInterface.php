<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 02.10.2017
 * Time: 18:32
 */

namespace AppBundle\FileManager\Cleaner;


use AppBundle\FileManager\FileManager;

interface CleanerInterface {
    public function getFileManager();
    public function setFileManager(FileManager $fileManager);
    public function clean();
} 