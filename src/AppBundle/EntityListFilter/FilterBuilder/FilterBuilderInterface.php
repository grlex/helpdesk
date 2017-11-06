<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 14.10.2017
 * Time: 14:56
 */

namespace AppBundle\EntityListFilter\FilterBuilder;

interface FilterBuilderInterface {
    public function supportsField($field, \Doctrine\ORM\Mapping\ClassMetadata $entityMetadata);
    public function getFieldFilter($field, $filterData, \Doctrine\ORM\Mapping\ClassMetadata $entityMetadata);
    public function supportsAssociation($association, \Doctrine\ORM\Mapping\ClassMetadata $entityMetadata);
    public function getAssociationFilter($association, $filterData, \Doctrine\ORM\Mapping\ClassMetadata $entityMetadata);
} 