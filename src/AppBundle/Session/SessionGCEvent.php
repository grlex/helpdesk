<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 02.10.2017
 * Time: 18:19
 */

namespace AppBundle\Session;


use Symfony\Component\EventDispatcher\Event;

class SessionGCEvent extends Event{

    private $lifetime;
    public function __construct($lifetime){
        $this->lifetime = $lifetime;
    }
    public function getLifetime(){
        return $this->lifetime;
    }

} 