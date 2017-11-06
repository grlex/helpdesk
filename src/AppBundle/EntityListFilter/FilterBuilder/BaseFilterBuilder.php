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
    protected function getFilterText(array $filterData, $name, $quote=false){
        if(empty($filterData[$name])) return false;
        $filterText = addslashes($filterData[$name]);
        if($quote) $filterText = sprintf("'%s'",$filterText);
        return $filterText;
    }
} 