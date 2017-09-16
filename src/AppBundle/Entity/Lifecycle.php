<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 04.09.2017
 * Time: 17:09
 */

namespace AppBundle\Entity;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Lifecycle
 * @package AppBundle\Model
 * @ORM\Entity
 * @ORM\Table(name="lifecycle")
 */
class Lifecycle {
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    public $id;
    /**
     * @var \DateTime Добавлена пользователем
     * @Assert\DateTime(message="model.common.datetime.format")
     * @ORM\Column(type="datetime", nullable=true)
     *
     */
    public $opened;
    /**
     * @var \DateTime Назначена исполнителю
     * @Assert\DateTime(message="model.common.datetime.format")
     * @ORM\Column(type="datetime", nullable=true)
     */
    public $distributed;
    /**
     * @var \DateTime Обработана исполнителем
     * @Assert\DateTime(message="model.common.datetime.format")
     * @ORM\Column(type="datetime", nullable=true)
     */
    public $processing;
    /**
     * @var \DateTime Проверена
     * @Assert\DateTime(message="model.common.datetime.format")
     * @ORM\Column(type="datetime", nullable=true)
     */
    public $checking;
    /**
     * @var \DateTime Закрыта
     * @Assert\DateTime(message="model.common.datetime.format")
     * @ORM\Column(type="datetime", nullable=true)
     */
    public $closed;


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
     * Set opened
     *
     * @param \DateTime $opened
     *
     * @return Lifecycle
     */
    public function setOpened($opened)
    {
        $this->opened = $opened;

        return $this;
    }

    /**
     * Get opened
     *
     * @return \DateTime
     */
    public function getOpened()
    {
        return $this->opened;
    }

    /**
     * Set distributed
     *
     * @param \DateTime $distributed
     *
     * @return Lifecycle
     */
    public function setDistributed($distributed)
    {
        $this->distributed = $distributed;

        return $this;
    }

    /**
     * Get distributed
     *
     * @return \DateTime
     */
    public function getDistributed()
    {
        return $this->distributed;
    }

    /**
     * Set processing
     *
     * @param \DateTime $processing
     *
     * @return Lifecycle
     */
    public function setProcessing($processing)
    {
        $this->processing = $processing;

        return $this;
    }

    /**
     * Get processing
     *
     * @return \DateTime
     */
    public function getProcessing()
    {
        return $this->processing;
    }

    /**
     * Set checking
     *
     * @param \DateTime $checking
     *
     * @return Lifecycle
     */
    public function setChecking($checking)
    {
        $this->checking = $checking;

        return $this;
    }

    /**
     * Get checking
     *
     * @return \DateTime
     */
    public function getChecking()
    {
        return $this->checking;
    }

    /**
     * Set closed
     *
     * @param \DateTime $closed
     *
     * @return Lifecycle
     */
    public function setClosed($closed)
    {
        $this->closed = $closed;

        return $this;
    }

    /**
     * Get closed
     *
     * @return \DateTime
     */
    public function getClosed()
    {
        return $this->closed;
    }
}
