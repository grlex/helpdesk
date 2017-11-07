<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 04.09.2017
 * Time: 16:46
 */

namespace AppBundle\Entity;
use Symfony\Component\Security\Core\Role\RoleInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;


class Role implements RoleInterface {
    const ROLE_ADMIN = 'ROLE_ADMIN';
    const ROLE_MODERATOR = 'ROLE_MODERATOR';
    const ROLE_EXECUTOR = 'ROLE_EXECUTOR';
    const ROLE_USER = 'ROLE_USER';

    protected static $roles = array(
        self::ROLE_ADMIN => 'ROLE_ADMIN',
        self::ROLE_MODERATOR => 'ROLE_MODERATOR',
        self::ROLE_EXECUTOR => 'ROLE_EXECUTOR',
        self::ROLE_USER => 'ROLE_USER'
    );
    protected static $maskBits = array(
        self::ROLE_ADMIN => 1,
        self::ROLE_MODERATOR => 2,
        self::ROLE_EXECUTOR => 4,
        self::ROLE_USER => 8,
    );

    public static function getRoles(){
        return self::$roles;
    }
    public static function getMaskBits(){
        return self::$maskBits;
    }

    protected $roleId;
    public function __construct($roleId){
        if( !array_key_exists($roleId, self::$roles)) $roleId = self::ROLE_USER;
        $this->roleId = $roleId;
    }


    public function __toString(){
        return $this->getRoleText();
    }

    public function getRole(){
        return $this->roleId;
    }
    public function getRoleText(){
        return self::$roles[$this->roleId];
    }
    public function getMaskBit(){
        return self::$maskBits[$this->roleId];
    }

    public static function translateTextRoles(TranslatorInterface $translator, $prefix="", $domain="messages"){
        foreach(self::$roles as &$textRole)
            $textRole = $translator->trans($prefix.$textRole,[],$domain);
    }
}
