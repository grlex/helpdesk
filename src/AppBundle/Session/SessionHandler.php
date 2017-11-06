<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AppBundle\Session;





use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher;

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
    private $dispatcher;
    public function __construct(EventDispatcher $dispatcher){
        $this->dispatcher = $dispatcher;
    }

    public function gc($lifetime){

        $this->dispatcher->dispatch('session.gc', new SessionGCEvent($lifetime));

        return parent::gc($lifetime);

    }
}
