<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 04.09.2017
 * Time: 17:30
 */

namespace AppBundle\Entity;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
/**
 * Class Active
 * @package AppBundle\Model
 * @ORM\Entity
 * @ORM\Table(name="active")
 * @UniqueEntity({"department","cabNumber"}, message="active.already.exists")
 */
class Active extends BaseEntity {

    public static function getFields() {
        return ['id', 'department', 'cabNumber'];
    }
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    /**
     * @var string Номер кабинета в отделе
     * @ORM\Column(type="string", length=50)
     * @Assert\NotBlank(message="entity.common.notBlank")
     * @Assert\Length( max = 50, maxMessage="entity.common.strLength.{{limit}}" )
     */
    private $cabNumber;
    /**
     * @var Department Отдел компании
     * @ORM\ManyToOne(targetEntity="Department")
     * @ORM\JoinColumn(onDelete="SET NULL", nullable=true)
     * @Assert\NotBlank(message="entity.common.notBlank")
     */
    private $department;


    public function __toString(){
        return $this->getCabNumber().', '.$this->getDepartment();
    }
    public static function getToStringFields(){
        return ['cabNumber', 'department'];
    }


    /* ============================== ============= */

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
     * Set cabNumber
     *
     * @param string $cabNumber
     *
     * @return Active
     */
    public function setCabNumber($cabNumber)
    {
        $this->cabNumber = $cabNumber;

        return $this;
    }

    /**
     * Get cabNumber
     *
     * @return string
     */
    public function getCabNumber()
    {
        return $this->cabNumber;
    }

    /**
     * Set department
     *
     * @param \AppBundle\Entity\Department $department
     *
     * @return Active
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

}
