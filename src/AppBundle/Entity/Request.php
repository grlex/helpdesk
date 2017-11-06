<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 04.09.2017
 * Time: 16:47
 */

namespace AppBundle\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Request
 * @package AppBundle\Model
 * @ORM\Entity
 * @ORM\Table(name="request")
 * @ORM\HasLifecycleCallbacks
 */
class Request extends BaseEntity
{
    public static function getFields() {
        return  ['id', 'name', 'description', 'status', 'textStatus', 'priority', 'textPriority',
        'active', 'files', 'category', 'user', 'executor', 'lifecycleSteps', 'thread'];
    }
    const STATUS_OPENED = 1;
    const STATUS_DISTRIBUTED = 2;
    const STATUS_REJECTED = 3;
    const STATUS_PROCESSED = 4;
    const STATUS_ACCEPTED = 5;
    const STATUS_DISCARDED = 6;
    const STATUS_CLOSED = 7;

    protected static  $statuses = array(
        self::STATUS_OPENED => 'opened',
        self::STATUS_DISTRIBUTED => 'distributed',
        self::STATUS_REJECTED => 'rejected',
        self::STATUS_PROCESSED => 'processed',
        self::STATUS_ACCEPTED => 'accepted',
        self::STATUS_DISCARDED => 'discarded',
        self::STATUS_CLOSED => 'closed',
    );

    const PRIORITY_LOW = 1;
    const PRIORITY_MEDIUM = 2;
    const PRIORITY_HIGH = 3;
    const PRIORITY_CRITICAL = 4;

    protected static  $priorities = array(
        self::PRIORITY_LOW => 'low',
        self::PRIORITY_MEDIUM => 'medium',
        self::PRIORITY_HIGH => 'high',
        self::PRIORITY_CRITICAL => 'critical',
    );


    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @var string Название заявки
     * @ORM\Column(type="string", length=50)
     * @Assert\NotBlank(message="entity.common.notBlank")
     * @Assert\Length( max = 50, maxMessage="model.common.strLength.{{limit}}" )
     */
    protected $name;
    /**
     * @var string Описание заявки
     * @ORM\Column(type="text",  nullable=true)
     */
    protected $description;

    /**
     * @var int Статус
     * @ORM\Column(type="integer", options={ "default": 1} )
     */
    protected $status;
    /**
     * @var int Приоритет
     * @ORM\Column(type="integer", options={ "default": 2} )
     */
    protected $priority;
    /**
     * @var Active Кабинет в отделе
     * @ORM\ManyToOne(targetEntity="Active")
     * @ORM\JoinColumn(name="active_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    protected $active;
    /**
     * @var File[] Файлы, прикрепленные к заявке
     * @ORM\ManyToMany(targetEntity="File", cascade={"persist", "remove"}, orphanRemoval=true )
     * @ORM\JoinTable(name="request_file",
     *      joinColumns = { @ORM\JoinColumn(name="request_id", referencedColumnName="id", onDelete="CASCADE") },
     *      inverseJoinColumns = { @ORM\JoinColumn(name="file_id", referencedColumnName="id", onDelete="CASCADE") } )
     */
    protected $files;
    /**
     * @var Category Категрия проблемы
     * @ORM\ManyToOne(targetEntity="Category")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    protected $category;
    /**
     * @var User Автор заявки о проблеме
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    protected $user;
    /**
     * @var User Кто будет решать проблему
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="executor_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    protected $executor;

    /**
     * @var LifecycleStep[] Временные отметки изменения статуса текущей заявки
     * @ORM\OneToMany(targetEntity="LifecycleStep", mappedBy="request", cascade={"persist","remove"})
     */
    protected $lifecycleSteps;

    /**
     * @var Thread
     * @ORM\OneToOne(targetEntity="Thread", mappedBy="request",  cascade={"persist","remove"})
     */
    protected $thread;


    public function __toString()
    {
        return $this->getName();
    }

    public static function getToStringFields(){
        return ['name'];
    }


    public static function &getPriorities(){
        return self::$priorities;
    }
    public static function &getStatuses(){

        return self::$statuses;
    }
    public function getTextStatus(){

        return $this->status ? self::$statuses[$this->status] : '';
    }
    public function getTextPriority(){
        return $this->priority ? self::$priorities[$this->priority] : '';
    }

    public function __construct()
    {
        $this->files = new \Doctrine\Common\Collections\ArrayCollection();
        $this->lifecycleSteps = new \Doctrine\Common\Collections\ArrayCollection();
    }


    /* ====================================== ============= */



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
     * @return Request
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
     * Set description
     *
     * @param string $description
     *
     * @return Request
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set status
     *
     * @param integer $status
     *
     * @return Request
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set priority
     *
     * @param integer $priority
     *
     * @return Request
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * Get priority
     *
     * @return integer
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * Set active
     *
     * @param \AppBundle\Entity\Active $active
     *
     * @return Request
     */
    public function setActive(\AppBundle\Entity\Active $active = null)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active
     *
     * @return \AppBundle\Entity\Active
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Add file
     *
     * @param \AppBundle\Entity\File $file
     *
     * @return Request
     */
    public function addFile(\AppBundle\Entity\File $file)
    {
        $this->files[] = $file;

        return $this;
    }

    /**
     * Remove file
     *
     * @param \AppBundle\Entity\File $file
     */
    public function removeFile(\AppBundle\Entity\File $file)
    {
        $this->files->removeElement($file);
    }

    /**
     * Get files
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * Set category
     *
     * @param \AppBundle\Entity\Category $category
     *
     * @return Request
     */
    public function setCategory(\AppBundle\Entity\Category $category = null)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return \AppBundle\Entity\Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     *
     * @return Request
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

    /**
     * Set executor
     *
     * @param \AppBundle\Entity\User $executor
     *
     * @return Request
     */
    public function setExecutor(\AppBundle\Entity\User $executor = null)
    {
        $this->executor = $executor;

        return $this;
    }

    /**
     * Get executor
     *
     * @return \AppBundle\Entity\User
     */
    public function getExecutor()
    {
        return $this->executor;
    }

    /**
     * Add lifecycleStep
     *
     * @param \AppBundle\Entity\LifecycleStep $lifecycleStep
     *
     * @return Request
     */
    public function addLifecycleStep(\AppBundle\Entity\LifecycleStep $lifecycleStep)
    {
        $lifecycleStep->setRequest($this);
        $this->lifecycleSteps[] = $lifecycleStep;

        return $this;
    }

    /**
     * Remove lifecycleStep
     *
     * @param \AppBundle\Entity\LifecycleStep $lifecycleStep
     */
    public function removeLifecycleStep(\AppBundle\Entity\LifecycleStep $lifecycleStep)
    {
        $this->lifecycleSteps->removeElement($lifecycleStep);
    }

    /**
     * Get lifecycleSteps
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getLifecycleSteps()
    {
        return $this->lifecycleSteps;
    }

    /**
     * Set thread
     *
     * @param \AppBundle\Entity\Thread $thread
     *
     * @return Request
     */
    public function setThread(\AppBundle\Entity\Thread $thread = null)
    {
        $this->thread = $thread;
        $thread->setRequest($this);

        return $this;
    }

    /**
     * Get thread
     *
     * @return \AppBundle\Entity\Thread
     */
    public function getThread()
    {
        return $this->thread;

    }
}
