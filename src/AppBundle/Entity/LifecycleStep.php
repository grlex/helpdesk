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
 * @ORM\Table(name="lifecycle_step")
 */
class LifecycleStep {

    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    protected $requestStatus;

    /**
     * @var \DateTime
     * @Assert\DateTime(message="model.common.datetime.format")
     * @ORM\Column(type="datetime")
     */
    protected $datetime;

    /**
     * @var User $user
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    protected $user;

    /**
     * @var Comment
     * @ORM\OneToOne(targetEntity="Comment")
     * @ORM\JoinColumn(onDelete="SET NULL", nullable=true)
     */
    protected $comment;


    /**
     * @var Request
     * @ORM\ManyToOne(targetEntity="Request", inversedBy="lifecycleSteps")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $request;



    // ------------------------ --------------- //


   

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
     * Set requestStatus
     *
     * @param integer $requestStatus
     *
     * @return LifecycleStep
     */
    public function setRequestStatus($requestStatus)
    {
        $this->requestStatus = $requestStatus;

        return $this;
    }

    /**
     * Get requestStatus
     *
     * @return integer
     */
    public function getRequestStatus()
    {
        return $this->requestStatus;
    }

    /**
     * Set datetime
     *
     * @param \DateTime $datetime
     *
     * @return LifecycleStep
     */
    public function setDatetime($datetime)
    {
        $this->datetime = $datetime;

        return $this;
    }

    /**
     * Get datetime
     *
     * @return \DateTime
     */
    public function getDatetime()
    {
        return $this->datetime;
    }

    /**
     * Set comment
     *
     * @param \AppBundle\Entity\Comment $comment
     *
     * @return LifecycleStep
     */
    public function setComment(\AppBundle\Entity\Comment $comment = null)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment
     *
     * @return \AppBundle\Entity\Comment
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set request
     *
     * @param \AppBundle\Entity\Request $request
     *
     * @return LifecycleStep
     */
    public function setRequest(\AppBundle\Entity\Request $request = null)
    {
        $this->request = $request;

        return $this;
    }

    /**
     * Get request
     *
     * @return \AppBundle\Entity\Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     *
     * @return User
     */
    public function setUser(\AppBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \AppBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }
}
