<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 13.09.2017
 * Time: 23:07
 */

namespace AppBundle\Service;


class TwigExtension  extends \Twig_Extension{

    public function getTests()
    {
        return array(
            new \Twig_SimpleTest('typeof', function ($value, $type) {
                $func = 'is_'.strtolower($type);
                if(function_exists($func)) return $func($value);
                if($value instanceof $type) return true;
                return false;
            })
        );
    }
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('onespace', function ($value) {

                $value = preg_replace('/(?>\s+)/',' ',$value);
                //$value = preg_replace('/\n/',' ',$value);
                return $value;
            })
        );
    }
}