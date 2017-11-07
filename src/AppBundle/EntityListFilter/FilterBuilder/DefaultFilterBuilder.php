<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 14.10.2017
 * Time: 15:01
 */

namespace AppBundle\EntityListFilter\FilterBuilder;




use Doctrine\ORM\Query\Expr;

class DefaultFilterBuilder extends BaseFilterBuilder {


    public function supportsField($field, \Doctrine\ORM\Mapping\ClassMetadata $entityMetadata)
    {
        return true;
    }

    public function getFieldFilter($field, $filterData, \Doctrine\ORM\Mapping\ClassMetadata $entityMetadata)
    {
        $filterText = $this->getFilterPart($filterData, $field);
        if($filterText===false) return [];

        $filterText = $filterData[$field];
        $filterText = addslashes($filterText);

        $arg = sprintf("'%%%s%%'",$filterText);
        $field = sprintf("%s.%s",$this->entityAlias, $field);
        return array(
            'expr' => $this->expr->like($field, $arg)
        );
    }
    public function supportsAssociation($association, \Doctrine\ORM\Mapping\ClassMetadata $entityMetadata)
    {
        return true;
    }

    public function getAssociationFilter($association, $filterData, \Doctrine\ORM\Mapping\ClassMetadata $entityMetadata)
    {

        $filterText = $this->getFilterPart($filterData, $association);
        if($filterText===false) return [];

        $mapping = $entityMetadata->getAssociationMapping($association);

        $targetEntity = $mapping['targetEntity'];

        if(method_exists($targetEntity,'getToStringFields')) {
            $toStringFields = [$targetEntity, 'getToStringFields'];
            $toStringFields = call_user_func($toStringFields);
        }
        else{
            return [];
        }

        $associationAlias = $entityMetadata->table['name'].$association;

        $arg = sprintf("'%%%s%%'",$filterText);
        $expressions = [];

        foreach ($toStringFields as $field) {
            $field = sprintf("%s.%s", $associationAlias, $field);
            $expressions[] = $this->expr->like($field, $arg);
        }
        $result = [];
        if (!empty($expressions)) {
            $result['expr'] = count($expressions) == 1 ? $expressions[0] : new Expr\Orx($expressions);
            $result['joins'][$associationAlias] = sprintf('%s.%s', $this->entityAlias, $association);
        }


        return $result;
    }
}