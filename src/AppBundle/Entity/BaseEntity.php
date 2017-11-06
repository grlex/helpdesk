<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 12.09.2017
 * Time: 20:34
 */

namespace AppBundle\Entity;


use Doctrine\ORM\Mapping\Entity;

abstract class BaseEntity implements \Iterator, \ArrayAccess, \Countable {
    use TraversableEntityTrait;
    protected $iterationPosition = 0;
    public  static function getFields(){
        return ['id'];
    }
    public static  function getToStringFields(){
        // фильтрация записей по столбцам с коллекцией объектов,
        // перечисленных в строковом представлении
        return [];
    }
    public abstract function __toString();

} 