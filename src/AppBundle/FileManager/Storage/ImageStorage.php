<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 30.09.2017
 * Time: 19:16
 */

namespace AppBundle\FileManager\Storage;


use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageStorage extends BaseStorage {

    const IMAGE_SMALL = 'small';
    const IMAGE_BIG = 'big';
    protected static $sideLengthLimitBig = false;
    protected static $sideLengthLimitSmall = 80;


    public function supportsExtension($extension){
        return in_array($extension, ['png', 'jpg', 'jpeg', 'bmp', 'gif']);
    }
    public function save(File $file, $uniqueValue=null)
    {
        $imageFunction = 'image';
        $srcResource = null;
        switch($file->guessExtension()){
            case 'png':
                $srcResource = imageCreateFromPng($file->getPathname());
                $imageFunction .= 'png';
                break;
            case 'jpg':
            case 'jpeg':
                $srcResource = imagecreatefromjpeg($file->getPathname());
                $imageFunction .= 'jpeg';
                break;
            case 'bmp':
                $srcResource = imagecreatefromwbmp($file->getPathname());
                $imageFunction .= 'wbmp';
                break;
            case 'gif':
                $srcResource = imagecreatefromgif($file->getPathname());
                $imageFunction .= 'gif';
                break;
            default:
                throw new FileException('file extension is not supported');

        }



        $srcSize = getimagesize($file->getPathname());
        $longestSideLength = max(width($srcSize), height($srcSize));

        $dstSizeBig = $longestSideLength <= self::$sideLengthLimitBig || self::$sideLengthLimitBig === false
                    ? $srcSize
                    : [
                            self::$sideLengthLimitBig * width($srcSize)/$longestSideLength,
                            self::$sideLengthLimitBig * height($srcSize)/$longestSideLength
                      ];
        $dstSizeSmall = $longestSideLength <= self::$sideLengthLimitSmall
                    ? $srcSize
                    : [
                        self::$sideLengthLimitSmall * width($srcSize)/$longestSideLength,
                        self::$sideLengthLimitSmall * height($srcSize)/$longestSideLength
                    ];

        $dstResourceBig = imagecreatetruecolor(width($dstSizeBig), height($dstSizeBig));
        $dstResourceSmall = imagecreatetruecolor(width($dstSizeSmall), height($dstSizeSmall));


        imagecopyresized($dstResourceBig, $srcResource,0,0,0,0,width($dstSizeBig),height($dstSizeBig),width($srcSize),height($srcSize));
        imagecopyresized($dstResourceSmall, $srcResource,0,0,0,0,width($dstSizeSmall),height($dstSizeSmall),width($srcSize),height($srcSize));

        $basename = $this->generateName($file, $uniqueValue);
        $imageFunction($dstResourceSmall, sprintf('%s/%s.%s',$this->path, self::IMAGE_SMALL, $basename));
        $imageFunction($dstResourceBig, sprintf('%s/%s.%s',$this->path, self::IMAGE_BIG, $basename));
        return $basename;

    }

    public function get( $baseName, $prefix=''){
        $prefix = empty($prefix) ? 'big' : $prefix;
        $name = $prefix.'.'.$baseName;
        $path = sprintf('%s/%s',$this->path, $name);

        return file_exists($path) ? new File(sprintf('%s/%s',$this->path, $name)) : null;
    }

    public function getPrefixes(){
        return [ self::IMAGE_BIG, self::IMAGE_SMALL];
    }

}

function width(array $size){
    return $size[0];
}
function height(array $size){
    return $size[1];
}