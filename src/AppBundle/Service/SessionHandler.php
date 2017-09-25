<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AppBundle\Service;




use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NativeFileSessionHandler;

/**
 * NativeFileSessionHandler.
 *
 * Native session handler using PHP's built in file storage.
 *
 * @author Drak <drak@zikula.org>
 */
class SessionHandler extends NativeFileSessionHandler
{
    private $fileStoragePath;
    private $doctrine;
    public function __construct($fileStoragePath, Registry $doctrine){
        $this->fileStoragePath = $fileStoragePath;
        $this->doctrine = $doctrine;
    }
    public function destroy($sessionId){


        try{
            $sessionData = $this->read($sessionId);
            $sessionData = $this->unserialize_php($sessionData);
        }catch(\Exception $e){
            return parent::destroy($sessionId);
        }



        $attributeBag = new AttributeBag();
        if(is_array($sessionData) && array_key_exists($attributeBag->getStorageKey(),$sessionData)) {
            $attributeBag->initialize($sessionData[$attributeBag->getStorageKey()]);

            FileStorage::onSessionDestroy($attributeBag, $this->fileStoragePath, $this->doctrine);
        }

        return parent::destroy($sessionId);

    }
    private  function unserialize_php($session_data) {
        $return_data = array();
        $offset = 0;
        while ($offset < strlen($session_data)) {
            if (!strstr(substr($session_data, $offset), "|")) {
                throw new Exception("invalid data, remaining: " . substr($session_data, $offset));
            }
            $pos = strpos($session_data, "|", $offset);
            $num = $pos - $offset;
            $varname = substr($session_data, $offset, $num);
            $offset += $num + 1;
            $data = unserialize(substr($session_data, $offset));
            $return_data[$varname] = $data;
            $offset += strlen(serialize($data));
        }
        return $return_data;
    }
}
