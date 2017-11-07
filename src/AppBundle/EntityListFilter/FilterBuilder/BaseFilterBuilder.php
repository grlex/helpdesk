<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 14.10.2017
 * Time: 17:24
 */

namespace AppBundle\EntityListFilter\FilterBuilder;


use Doctrine\ORM\Query\Expr;

abstract class BaseFilterBuilder implements FilterBuilderInterface{
    protected $entityAlias;
    protected $expr;
    public function __construct($entityAlias = 'e'){
        $this->entityAlias = $entityAlias;
        $this->expr = new Expr();
    }
    protected function getFilterPart(array $filterData, $name, $quote=false){
        if(empty($filterData[$name])) return false;
        $part = $filterData[$name];
        if(is_array($part)){
            foreach($part as $key=>&$partElem) $partElem = $this->getFilterPart($part,$key, $quote);
            return $part;
        }
        if(is_string($part)) $part = addslashes($part);
        if($quote) $part = sprintf("'%s'",$part);
        return $part;
    }
} 