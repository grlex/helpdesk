<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 14.10.2017
 * Time: 14:54
 */
namespace AppBundle\EntityListFilter;

use AppBundle\EntityListFilter\FilterBuilder\FilterBuilderInterface;
use AppBundle\EntityListFilter\FilterBuilder\DefaultFilterBuilder;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;

class EntityListFilter  {
    const FILTER_STRATEGY_OR=1;
    const FILTER_STRATEGY_AND=2;
    private $entityMetadata;
    private $filterData;
    private $expr;
    private $builder;
    private $filterStrategy;

    public function __construct(array $filterData,
                                \Doctrine\ORM\Mapping\ClassMetadata $entityMetadata,
                                $filterStrategy = self::FILTER_STRATEGY_AND){
        $this->entityMetadata = $entityMetadata;
        $this->filterData = $filterData;
        $this->filterStrategy = $filterStrategy;
        $this->expr = new Expr();
        $this->builder = new DefaultFilterBuilder;

    }


    public function setBuilder(FilterBuilderInterface $builder){
        $this->builder = $builder;
    }
    public function getBuilder(){
        return $this->builder;
    }


    public function applyFilter( QueryBuilder $queryBuilder ){

        $this->expr = new Expr();

        $filter = $this->buildFilter(array(
            'fields'=>$this->entityMetadata->getFieldNames(),
            'associations'=>$this->entityMetadata->getAssociationNames()
        ));



        $combiner = '';
        switch($this->filterStrategy){
            case self::FILTER_STRATEGY_AND:
                $combiner = Expr\Andx::class;
                break;
            case self::FILTER_STRATEGY_OR:
                $combiner =  Expr\Orx::class;
                break;
        }
        if(!empty($filter['expressions'])){
            $expr = count($filter['expressions'])==1 ? $filter['expressions'][0] : new $combiner($filter['expressions']);
            $queryBuilder->andWhere($expr);
        }

        foreach($filter['joins'] as $alias => $join) $queryBuilder->leftJoin($join, $alias);

    }

    private function buildFilter($membersList){
        $expressions = [];
        $joins = [];
        foreach($membersList as $memberType=>$members) {

            foreach ($members as $member) {
                //$filterText = array_key_exists($member, $this->filterData) ? $this->filterData[$member] : false;
                $filter = $this->buildMemberFilter($member, $memberType, $this->filterData, $this->entityMetadata);

                if (!is_array($filter)) continue;
                if (array_key_exists('expr', $filter)) $expressions[] = $filter['expr'];
                if (array_key_exists('joins', $filter) and is_array($filter['joins']))
                    foreach ($filter['joins'] as $alias => $join)
                        $joins[$alias] = $join;

            }
        }
        return ['expressions' => $expressions, 'joins' => $joins];
    }

    private function buildMemberFilter($name, $memberType, $filterData,  $entityMetadata){

        switch($memberType){
            case 'fields':
                if($this->builder->supportsField($name, $entityMetadata))
                    return $this->builder->getFieldFilter($name, $filterData, $entityMetadata);
                break;
            case 'associations':
                if($this->builder->supportsAssociation($name, $entityMetadata))
                    return $this->builder->getAssociationFilter($name, $filterData, $entityMetadata);
                break;
        }

        return false;

    }


/*
    private function ($field, $filterText,  $entityMetadata){
        $choices = $originalForm[$name]->getConfig()->getOptions()['choices'];
        $matchedChoices = [];
        foreach($choices as $choice=>$val)
            $matchedChoices[$this->get('translator')->trans($choice,[],'forms')] = $val;

        foreach($matchedChoices as $choice=>$val){
            if( stristr($choice, $value)) continue ;
            unset($matchedChoices[$choice]);
        }
        if(empty($matchedChoices))  $matchedChoices[] = -1;
        return [ 'expr'=> $this->expr->in('e.'.$name, $matchedChoices)];

    }
    private function getEntityFieldFilter($field, $filterText,  $entityMetadata){
        $entityClass = $originalForm[$name]->getConfig()->getOptions()['class'];
        $toStringFields = [$entityClass,'getToStringFields'];
        $entityExpressions = [];
        $joins = [];
        $expr = null;
        foreach($toStringFields() as $toStringField) {
            $arg = sprintf("'%%%s%%'",$value);
            $entityExpressions[] = $this->expr->like($name.'.'.$toStringField, $arg );
        }
        if(!empty($entityExpressions)) {
            $joins['e.'.$name] = $name;
            $expr = count($entityExpressions) == 1 ? $entityExpressions[0] : new Expr\Orx($entityExpressions);
        }
        return  [ 'expr' => $expr, 'joins' => $joins ];
    }

    private function getDefaultFilter($field, $filterText,  $entityMetadata){
        $arg = sprintf("'%%%s%%'", $value);
        return [ 'expr' => $this->expr->like('e.'.$name, $arg ) ];
    }
*/


} 