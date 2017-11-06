<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 04.09.2017
 * Time: 16:46
 */

namespace AppBundle\Entity;
use Symfony\Component\Security\Core\Role\RoleInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Class Role
 * @package AppBundle\Model
 * @ORM\Entity
 * @ORM\Table(name="role")
 * @UniqueEntity("role")
 */
class Role implements RoleInterface {
    public static function getFields() {
        return  ['id', 'role' ];
    }
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @var string
     * @ORM\Column(type="string", length=20)
     */
    protected $role;
    public function __construct($roleName){
        $this->$role = $roleName;
    }


    public function getToStringFields(){
        return ['role'];
    }

    public function __toString(){
        return $this->getRole();
    }


    /* =================== ======== */
    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    public function getRole(){
        return $this->role;
    }
}
