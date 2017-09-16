<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 08.09.2017
 * Time: 0:05
 */

namespace AppBundle\Entity;


trait TraversableEntityTrait {

    /**
     * The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset)
    {
        $getter = 'get'.\ucfirst($offset);
        return method_exists($this,$getter);
    }

    /**
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {
        $getter = 'get'.\ucfirst($offset);
        return call_user_func([$this,$getter]);
    }

    /**
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $setter = 'set'.\ucfirst($offset);
        if(method_exists($this,$setter))
            \call_user_func([$this, $setter]);
    }

    /**
     * @return void
     */
    public function offsetUnset($offset)
    {
        $this->offsetSet($offset,null);
    }

    /**
     * @return mixed Can return any type.
     */
    public function current()
    {
        $field = $this->fields[$this->iterationPosition];
        return $this[$field];
    }

    /**
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        $this->iterationPosition++;
    }

    /**
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
        return $this->fields[$this->iterationPosition];
    }

    /**
     * Returns true on success or false on failure.
     */
    public function valid()
    {
        return $this->iterationPosition < count($this->fields);
    }

    /**
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        $this->iterationPosition=0;
    }

    /**
     * The return value is cast to an integer.
     */
    public function count()
    {
        return count($this->fields);
    }
} 