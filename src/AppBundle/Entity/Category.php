<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 04.09.2017
 * Time: 17:27
 */

namespace AppBundle\Entity;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Category
 * @package AppBundle\Model
 * @ORM\Entity
 * @ORM\Table(name="category")
 */
class Category {
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    public $id;
    /**
     * @var string Название категории
     * @ORM\Column(type="string", length=50)
     * @Assert\NotBlank(message="entity.common.notBlank")
     * @Assert\Length( max = 50, maxMessage="model.common.strLength.{{limit}}" )
     */
    public $name;

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
     * @return Category
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
}
