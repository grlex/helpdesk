<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 25.10.2017
 * Time: 18:58
 */

namespace AppBundle\Security;

use AppBundle\Entity\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class RequestEntityVoter extends Voter
{
    const ACTION_LIST = 'list';
    const ACTION_EDIT = 'edit';
    const ACTION_REMOVE = 'remove';
    const ACTION_VIEW = 'view';
    const ACTION_REJECT = 'reject';
    const ACTION_REOPEN = 'reopen';
    const ACTION_DISTRIBUTE = 'distribute';
    const ACTION_PROCESS = 'process';
    const ACTION_ACCEPT = 'accept';
    const ACTION_DISCARD = 'discard';
    const ACTION_CLOSE = 'close';
    protected static $actions = array(
        self::ACTION_LIST,
        self::ACTION_EDIT ,
        self::ACTION_REMOVE,
        self::ACTION_VIEW ,
        self::ACTION_REJECT ,
        self::ACTION_REOPEN ,
        self::ACTION_DISTRIBUTE ,
        self::ACTION_PROCESS ,
        self::ACTION_ACCEPT ,
        self::ACTION_DISCARD ,
        self::ACTION_CLOSE
    );





    protected function voteOnAttribute($action, $request, TokenInterface $token)
    {

        if(!$token->getUser()) return false;

        $user = $token->getUser();
        $requestUser = $request->getUser();
        $requestExecutor = $request->getExecutor();

        if($action=='view' or $action=='list') {
            return true;
        }
        if($action=='edit') {
            if($user->hasRole('ROLE_ADMIN')) return true;
            return false;
        }
        if($action=='remove') {
            if($user->hasRole('ROLE_ADMIN')) return true;
            return false;
        }


        switch($request->getStatus()){
            case Request::STATUS_OPENED;
                if(!($action=='reject' or $action=='distribute')) return false;
                //if($user->hasRole('ROLE_ADMIN')) return true;
                if(!$user->hasRole('ROLE_MODERATOR')) return false;
                return true;
            case Request::STATUS_REJECTED;
                if(!($action=='reopen')) return false;
                //if($user->hasRole('ROLE_ADMIN')) return true;
                if(!$user->hasRole('ROLE_MODERATOR')) return false;
                return true;
            case Request::STATUS_DISTRIBUTED;
                if(!($action=='process')) return false;
                //if($user->hasRole('ROLE_ADMIN')) return true;
                if(!$user->hasRole('ROLE_EXECUTOR')) return false;
                if(!$requestExecutor or $requestExecutor->getId()!=$user->getId()) return false;
                return true;
            case Request::STATUS_PROCESSED;
                if(!($action=='accept' or $action=='discard')) return false;
                //if($user->hasRole('ROLE_ADMIN')) return true;
                if(!$requestUser or $requestUser->getId()!=$user->getId()) return false;
                return true;
            case Request::STATUS_ACCEPTED;
                if(!($action=='close')) return false;
                //if($user->hasRole('ROLE_ADMIN')) return true;
                if(!$user->hasRole('ROLE_MODERATOR')) return false;
                return true;

            case Request::STATUS_DISCARDED;
                if(!($action=='close' or $action=='distribute')) return false;
                //if($user->hasRole('ROLE_ADMIN')) return true;
                if(!$user->hasRole('ROLE_MODERATOR')) return false;
                return true;
            case Request::STATUS_CLOSED;
                return false;
        }

    }


    protected function supports($attribute, $subject)
    {
        return $subject instanceof Request
            and in_array($attribute, self::$actions);
    }
}