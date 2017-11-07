<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 14.10.2017
 * Time: 15:01
 */

namespace AppBundle\EntityListFilter\FilterBuilder;


use AppBundle\Entity\Request;
use AppBundle\Entity\Role;
use AppBundle\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\Query\Expr;
use Symfony\Component\Translation\TranslatorInterface;

class AppFilterBuilder extends DefaultFilterBuilder {
    private $translator;
    private $doctrine;
    public function __construct(TranslatorInterface $translator,
                                Registry $doctrine,
                                $entityAlias='e'){
        $this->translator = $translator;
        $this->doctrine = $doctrine;
        parent::__construct($entityAlias);
    }
    public function getFieldFilter($field, $filterData, \Doctrine\ORM\Mapping\ClassMetadata $entityMetadata){
        $result = false;
        switch($entityMetadata->getName()){
            case Request::class:
                $result = $this->getRequestFilter($field, $filterData, $entityMetadata);
                break;
            case User::class:
                $result = $this->getUserFilter($field, $filterData, $entityMetadata);
                break;
        }
        if($result !== false) return $result;
        return parent::getFieldFilter($field, $filterData, $entityMetadata);
    }

    public function getAssociationFilter($association, $filterData, \Doctrine\ORM\Mapping\ClassMetadata $entityMetadata){
        $result = false;
        switch($entityMetadata->getName()){
            case Request::class:
                $result = $this->getRequestFilter($association, $filterData, $entityMetadata);
                break;
        }
        if($result !== false) return $result;
        return parent::getAssociationFilter($association, $filterData, $entityMetadata);
    }

    private function getRequestFilter($member, $filterData, \Doctrine\ORM\Mapping\ClassMetadata $entityMetadata){

        switch($member){
            case 'status':
                $status = $this->getFilterPart($filterData,'status');
                if($status){
                    $statuses = is_array($status) ? $status : [$status];
                    $statuses = array_map(function($s){ return (int)$s; }, $statuses);
                }
                else {
                    $filterText = $this->getFilterPart($filterData, 'textStatus');
                    if ($filterText === false) return [];
                    $statuses = call_user_func([Request::class, 'getStatuses']);
                    foreach ($statuses as $status => $statusText) {
                        if (mb_stristr($statusText, $filterText)) continue;
                        unset($statuses[$status]);
                    }
                    if (empty($statuses)) $statuses = [-1];
                    $statuses = array_keys($statuses);
                }
                return [ 'expr'=> $this->expr->in(sprintf('%s.%s', $this->entityAlias, 'status'), $statuses)];
            case 'priority':
                $priority = $this->getFilterPart($filterData, 'priority');
                if($priority){
                    $priorities = is_array($priority) ? $priority : [$priority];
                }
                else {
                    $filterText = $this->getFilterPart($filterData, 'textPriority');
                    if ($filterText === false) return [];
                    $priorities = call_user_func([Request::class, 'getPriorities']);
                    foreach ($priorities as $priority => $priorityText) {
                        if (mb_stristr($priorityText, $filterText)) continue;
                        unset($priorities[$priority]);
                    }
                    if (empty($priorities)) $priorities = [-1];
                    $priorities = array_keys($priorities);
                }
                return [ 'expr'=> $this->expr->in(sprintf('%s.%s', $this->entityAlias, 'priority'), $priorities)];
            case 'user':
            case 'executor':
                $filterText = $this->getFilterPart($filterData, $member, true);
                if($filterText===false) return [];
                $memberAlias = 'request'.$member;
                return [ 'expr'=> $this->expr->eq(sprintf('%s.login', $memberAlias), $filterText),
                    'joins' => [$memberAlias => sprintf('%s.%s', $this->entityAlias, $member) ]
                ];

        }
        return false;
    }

    private function getUserFilter($member, $filterData, \Doctrine\ORM\Mapping\ClassMetadata $entityMetadata){
        switch($member){
            case 'rolesMask':
                $filterText = $this->getFilterPart($filterData, 'roles');
                if($filterText===false) return [];
                $rolesMask = 0;
                $rolesMasks = Role::getMaskBits();
                $roles = Role::getRoles();

                foreach($roles as $id => $textRole){
                    if(!mb_stristr($textRole, $filterText)) continue;
                    $rolesMask|=$rolesMasks[$id];
                }
                return [ 'expr'=> $this->expr->neq(
                    new Expr\Func('BIT_AND', [ $this->entityAlias.'.rolesMask', $rolesMask]),
                    0)
                ];

        }
        return false;
    }

}