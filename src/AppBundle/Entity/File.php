<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 21.09.2017
 * Time: 9:56
 */

namespace AppBundle\Entity;

use Symfony\Component\HttpFoundation\File\UploadedFile;


use Doctrine\ORM\Mapping as ORM;
/**
 * Class File
 * @package AppBundle\Model
 * @ORM\Entity
 * @ORM\Table(name="file")
 */
class File {
    /**
     * @var int
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;
    /**
     * @var string
     * @ORM\Column(type="string", length=50)
     */
    protected $originalName;
    /**
     * @var string
     * @ORM\Column(type="string", length=10)
     */
    protected $extension;
    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    protected $size;
    /**
     * @var bool
     * @ORM\Column(type="boolean", options={ "default":0}, nullable=true)
     */
    protected $confirmed;




    public function getName(){
        return  sprintf('%s.%s', $this->id, $this->extension);
    }


    // =========================== ========== //


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
     * Set originalName
     *
     * @param string $originalName
     *
     * @return File
     */
    public function setOriginalName($originalName)
    {
        $this->originalName = $originalName;

        return $this;
    }

    /**
     * Get originalName
     *
     * @return string
     */
    public function getOriginalName()
    {
        return $this->originalName;
    }

    /**
     * Set extension
     *
     * @param string $extension
     *
     * @return File
     */
    public function setExtension($extension)
    {
        $this->extension = $extension;

        return $this;
    }

    /**
     * Get extension
     *
     * @return string
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * Set size
     *
     * @param integer $size
     *
     * @return File
     */
    public function setSize($size)
    {
        $this->size = $size;

        return $this;
    }

    /**
     * Get size
     *
     * @return integer
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Set confirmed
     *
     * @param boolean $confirmed
     *
     * @return File
     */
    public function setConfirmed($confirmed)
    {
        $this->confirmed = $confirmed;

        return $this;
    }

    /**
     * Get confirmed
     *
     * @return boolean
     */
    public function getConfirmed()
    {
        return $this->confirmed;
    }
}
