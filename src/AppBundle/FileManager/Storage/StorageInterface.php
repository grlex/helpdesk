<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 30.09.2017
 * Time: 18:53
 */

namespace AppBundle\FileManager\Storage;


use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

interface StorageInterface {
    public function supportsExtension($extension);
    public function save(File $file, $uniqueValue = null);
    public function delete($baseName);
    public function get( $baseName, $prefix);
    public function getPrefixes();
} 