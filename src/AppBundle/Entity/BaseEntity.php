<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 12.09.2017
 * Time: 20:34
 */

namespace AppBundle\Entity;


use Doctrine\ORM\Mapping\Entity;

abstract class BaseEntity implements NamedEntityInterface, \Iterator, \ArrayAccess, \Countable {
    use TraversableEntityTrait;
    protected $iterationPosition = 0;
    protected $fields = ['id' ];
} 