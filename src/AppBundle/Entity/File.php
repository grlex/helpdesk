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
     * @ORM\Column(type="string", length=100)
     */
    protected $originalName;
    /**
     * @var bool
     * @ORM\Column(type="boolean", options={ "default":0}, nullable=true)
     */
    protected $confirmed;

    /**
     * @var string
     * @ORM\Column(type="string", length=50, unique=true)
     */
    protected $filename;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    protected $created;


    public function __toString(){
        return $this->originalName;
    }
    public function getToStringFields(){
        return ['originalName'];
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

    /**
     * Set filename
     *
     * @param string $filename
     *
     * @return File
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * Get filename
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return File
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

}
