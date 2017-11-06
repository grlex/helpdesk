<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 04.09.2017
 * Time: 16:27
 */

namespace AppBundle\Entity;
use Doctrine\Common\Collections\ArrayCollection;
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
        return ['id', 'name', 'login', 'password', 'roles', 'department', 'position'];
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
     * @var Role[] Роль пользователя в компании
     * @ORM\ManyToMany(targetEntity="Role")
     * ORM\JoinTable("user_role",
     *      joinColumns = {@ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns = { @ORM\JoinColumn(name="role_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     */
    private $roles;


    /**
     * @var bool Weather user is removed
     * @ORM\Column(type="boolean", options={ "default": 0 } )
     */
    private $removed=0;


    public function __construct(){
        $this->roles = new ArrayCollection();
    }

    public function hasRole($role){
        $roles = is_array($role) ? $role : [$role];

        return $this->roles->exists(function($index, $roleEntity) use ($roles){ return in_array($roleEntity->getRole(), $roles);});
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

    public function __toString(){
        return $this->getName();
    }

    public static function getToStringFields(){
        return ['name', 'login'];
    }

    /**
     * Only for set text role names for displaying in views
     * @param string[] $roles
     */
    public function setTextRoles($roles){
        $this->roles = $roles;
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
     * Add role
     *
     * @param \AppBundle\Entity\Role $role
     *
     * @return User
     */
    public function addRole(\AppBundle\Entity\Role $role)
    {
        $this->roles->add($role);

        return $this;
    }

    /**
     * Remove role
     *
     * @param \AppBundle\Entity\Role $role
     */
    public function removeRole(\AppBundle\Entity\Role $role)
    {
        $this->roles->removeElement($role);
    }


    public function getRoles(){
        return is_array($this->roles) ? $this->roles : $this->roles->toArray();
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
}
