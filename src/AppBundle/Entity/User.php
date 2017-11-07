<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 04.09.2017
 * Time: 16:27
 */

namespace AppBundle\Entity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Class User
 * @package AppBundle\Model
 * @ORM\Entity
 * @ORM\Table(name="user")
 * @UniqueEntity("login", message="user.already.exists")
 */
class User extends BaseEntity implements UserInterface {
    public static function getFields(){
        return ['id', 'name', 'login', 'password', 'rolesMask', 'roles', 'department', 'position'];
    }

    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    /**
     * @var string Имя пользователя
     * @ORM\Column(type="string", length=50)
     * @Assert\NotBlank(message="entity.common.notBlank")
     * @Assert\Length( max = 50, maxMessage="model.common.strLength.{{limit}}" )
     */
    private $name;
    /**
     * @var string Логин пользователя
     * @ORM\Column(type="string", length=50)
     * @Assert\NotBlank(message="entity.common.notBlank")
     * @Assert\Length( max = 50, maxMessage="model.common.strLength.{{limit}}" )
     */
    private $login;
    /**
     * @var string Пароль пользователя
     * @ORM\Column(type="string", length=60)
     * @Assert\NotBlank(message="entity.common.notBlank")
     * @Assert\Length( max = 60, maxMessage="model.common.strLength.{{limit}}" )
     */
    private $password;
    /**
     * @var string Название должности пользователя
     * @ORM\Column(type="string", length=50, nullable=true)
     * @Assert\NotBlank(message="entity.common.notBlank")
     * @Assert\Length( max = 50, maxMessage="model.common.strLength.{{limit}}" )
     */
    private $position;
    /**
     * @var Department Отдель, в котором работает пользователь
     * @ORM\ManyToOne(targetEntity="Department")
     * @ORM\JoinColumn(name="department_id", referencedColumnName="id", onDelete="SET NULL", nullable=true )
     */
    private $department;

    /**
     * @var integer Роль пользователя
     * @ORM\Column(type="smallint")
     */
    private $rolesMask;


    /**
     * @var bool Weather user is removed
     * @ORM\Column(type="boolean", options={ "default": 0 } )
     */
    private $removed=0;



    public function hasRole($roleId){
        $masks = Role::getMaskBits();
        $roleMask = array_key_exists($roleId, $masks) ? $masks[$roleId] : 0;
        return (bool)($this->rolesMask & $roleMask);
    }

    /* ===================== =========== */


    /**
     * @return string The password
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @return string The username
     */
    public function getUsername()
    {
        return  $this->login;
    }

    /**
     * @return string|null The salt
     */
    public function getSalt()
    {
        // TODO: Implement getSalt() method.
    }

    /**
     * Removes sensitive data from the user.
     */
    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }

    public function getRoles(){
        $roles = [];
        foreach(Role::getMaskBits() as $id => $bit){
            if($this->rolesMask & $bit) $roles[] = new Role($id);
        }
        return $roles;
    }

    /*public function getTextRoles(){
        $roles = Role::getRoles();
        foreach(Role::getMaskBits() as $id => $bit){
            if($this->rolesMask & $bit) continue;
            unset($roles[$id]);
        }
        return $roles;
    }*/

    public function addRole(\AppBundle\Entity\Role $role)
    {
        $this->rolesMask |= $role->getMaskBit();
        return $this;
    }


    public function removeRole(\AppBundle\Entity\Role $role)
    {
        $this->rolesMask ^= ~$role->getMaskBit();
        return $this;
    }




    public function __toString(){
        return $this->getName();
    }

    public static function getToStringFields(){
        return ['name', 'login'];
    }



    /* ----------------------------------------- */

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return User
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set login
     *
     * @param string $login
     *
     * @return User
     */
    public function setLogin($login)
    {
        $this->login = $login;

        return $this;
    }

    /**
     * Get login
     *
     * @return string
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * Set password
     *
     * @param string $password
     *
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Set position
     *
     * @param string $position
     *
     * @return User
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position
     *
     * @return string
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Get removed
     *
     * @return boolean
     */
    public function getRemoved()
    {
        return $this->removed;
    }

    /**
     * Set removed
     *
     * @param boolean $removed
     *
     * @return User
     */
    public function setRemoved($removed)
    {
        $this->removed = $removed;

        return $this;
    }

    /**
     * Get removed
     *
     * @return boolean
     */
    public function isRemoved()
    {
        return $this->removed;
    }

    /**
     * Set department
     *
     * @param \AppBundle\Entity\Department $department
     *
     * @return User
     */
    public function setDepartment(\AppBundle\Entity\Department $department = null)
    {
        $this->department = $department;

        return $this;
    }

    /**
     * Get department
     *
     * @return \AppBundle\Entity\Department
     */
    public function getDepartment()
    {
        return $this->department;
    }
    /**
     * Set rolesMask
     *
     * @param int $rolesMask
     *
     * @return User
     */
    public function setRolesMask($rolesMask = null)
    {
        $this->rolesMask = $rolesMask;
        return $this;
    }

    /**
     * Get rolesMask
     *
     * @return int
     */
    public function getRolesMask()
    {
        return $this->rolesMask;
    }
}
