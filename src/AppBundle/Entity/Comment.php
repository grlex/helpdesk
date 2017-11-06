<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 15.09.2017
 * Time: 10:49
 */

namespace AppBundle\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping as ORM;
use FOS\CommentBundle\Entity\Thread;
use FOS\CommentBundle\Model\RawCommentInterface;
use FOS\CommentBundle\Model\SignedCommentInterface;
use FOS\CommentBundle\Model\ThreadInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use FOS\CommentBundle\Entity\Comment as BaseComment;
use FOS\CommentBundle\Entity\Thread as BaseThread;

/**
 * Class Comment
 * @package AppBundle\Entity
 * @ORM\Entity
 * @ORM\Table(name="comment")
 */
class Comment extends BaseComment implements SignedCommentInterface, RawCommentInterface
{
    /**
     * @var int Id
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(onDelete="SET NULL", nullable=true)
     */
    protected $author;

    /**
     * @var BaseThread
     * @ORM\ManyToOne(targetEntity="Thread", inversedBy="comments")
     */
    protected $thread;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    protected $rawBody;


    /*public function __toString()
    {
        return $this->getBody();
    }*/

    public function getAuthorName(){
        return $this->author->getName();
    }



    // ===================================== ===================




    /**
     * Constructor
     */
    public function __construct()
    {
        $this->files = new \Doctrine\Common\Collections\ArrayCollection();
        parent::__construct();
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
     * Set rawBody
     *
     * @param string $rawBody
     *
     * @return Comment
     */
    public function setRawBody($rawBody)
    {
        $this->rawBody = $rawBody;

        return $this;
    }

    /**
     * Get rawBody
     *
     * @return string
     */
    public function getRawBody()
    {
        return $this->rawBody;
    }

    /**
     * Set author
     *
     * @param UserInterface $author
     *
     * @return Comment
     */
    public function setAuthor(UserInterface $author = null)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get author
     *
     * @return UserInterface
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Set thread
     *
     * @param BaseThread $thread
     *
     * @return Comment
     */
    public function setThread(ThreadInterface $thread = null)
    {
        $this->thread = $thread;

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
