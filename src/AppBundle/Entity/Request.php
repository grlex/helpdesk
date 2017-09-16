<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 04.09.2017
 * Time: 16:47
 */

namespace AppBundle\Entity;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Request
 * @package AppBundle\Model
 * @ORM\Entity
 * @ORM\Table(name="request")
 */
class Request extends BaseEntity{
    protected $fields = ['id','name','description','comment','status', 'priority',
                         'active', 'file','category','user','executor','lifecycle'];
    const STATUS_OPENED = 1;
    const STATUS_DISTRIBUTED = 2;
    const STATUS_PROCESSED = 3;
    const STATUS_CHECKING = 4;
    const STATUS_CLOSED = 5;
    //
    const PRIORITY_LOW = 1;
    const PRIORITY_MEDIUM = 2;
    const PRIORITY_HIGH = 3;
    const PRIORITY_CRITICAL = 4;
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    public $id;
    /**
     * @var string Название заявки
     * @ORM\Column(type="string", length=50)
     * @Assert\NotBlank(message="entity.common.notBlank")
     * @Assert\Length( max = 50, maxMessage="model.common.strLength.{{limit}}" )
     */
    private $name;
    /**
     * @var string Описание заявки
     * @ORM\Column(type="string", length=200, nullable=true)
     * @Assert\NotBlank(message="entity.common.notBlank")
     * @Assert\Length( max = 200, maxMessage="model.common.strLength.{{limit}}" )
     */
    private $description;

    /**
     * @var Comment[] Комментарий к заявке
     * @ORM\OneToMany(targetEntity="Comment", mappedBy="request")
     */
    private $comments;
    /**
     * @var int Статус
     * @ORM\Column(type="integer", options={ "default": 1} )
     */
    private $status;
    /**
     * @var int Приоритет
     * @ORM\Column(type="integer", options={ "default": 2} )
     */
    private $priority;
    /**
     * @var Active Кабинет в отделе
     * @ORM\ManyToOne(targetEntity="Active")
     * @ORM\JoinColumn(name="active_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    private $active;
    /**
     * @var string Файл с подробностями проблемы
     * @ORM\Column(type="string", length=200, nullable=true)
     */
    private $file;
    /**
     * @var Category Категрия проблемы
     * @ORM\ManyToOne(targetEntity="Category")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    private $category;
    /**
     * @var User Автор заявки о проблеме
     * @ORM\OneToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    private $user;
    /**
     * @var User Кто будет решать проблему
     * @ORM\OneToOne(targetEntity="User")
     * @ORM\JoinColumn(name="executor_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    private $executor;

    /**
     * @var Lifecycle Временные отметки изменения статуса текущей заявки
     * @ORM\OneToOne(targetEntity="Lifecycle")
     * @ORM\JoinColumn(name="lifecycle_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $lifecycle;



    public function __toString(){
        return $this->getName();
    }

    /* ====================================== ============= */
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->comments = new \Doctrine\Common\Collections\ArrayCollection();
    }

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
     * Set file
     *
     * @param string $file
     *
     * @return Request
     */
    public function setFile($file)
    {
        $this->file = $file;

        return $this;
    }

    /**
     * Get file
     *
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Add comment
     *
     * @param \AppBundle\Entity\Comment $comment
     *
     * @return Request
     */
    public function addComment(\AppBundle\Entity\Comment $comment)
    {
        $this->comments[] = $comment;

        return $this;
    }

    /**
     * Remove comment
     *
     * @param \AppBundle\Entity\Comment $comment
     */
    public function removeComment(\AppBundle\Entity\Comment $comment)
    {
        $this->comments->removeElement($comment);
    }

    /**
     * Get comments
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getComments()
    {
        return $this->comments;
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
     * Set lifecycle
     *
     * @param \AppBundle\Entity\Lifecycle $lifecycle
     *
     * @return Request
     */
    public function setLifecycle(\AppBundle\Entity\Lifecycle $lifecycle = null)
    {
        $this->lifecycle = $lifecycle;

        return $this;
    }

    /**
     * Get lifecycle
     *
     * @return \AppBundle\Entity\Lifecycle
     */
    public function getLifecycle()
    {
        return $this->lifecycle;
    }
}
