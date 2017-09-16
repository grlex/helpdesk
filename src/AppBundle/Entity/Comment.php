<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 15.09.2017
 * Time: 10:49
 */

namespace AppBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Comment
 * @package AppBundle\Entity
 * @ORM\Entity
 * @ORM\Table(name="comment")
 */
class Comment
{
    /**
     * @var int Id
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    /**
     * @var Request
     * @ORM\ManyToOne(targetEntity="Request", inversedBy="comments")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $request;
    /**
     * @var User
     * @ORM\OneToOne(targetEntity="User")
     * @ORM\JoinColumn(onDelete="SET NULL", nullable=true)
     */
    private $author;
    /**
     * @var string Text of the comment
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     */
    private $content;
    /**
     * @var \DateTime Time when comment was added
     * @ORM\Column(type="datetime")
     * @Assert\NotBlank()
     */
    private $datetime;

    /**
     * @var Comment Parent of this comment
     * @ORM\ManyToOne(targetEntity="comment", inversedBy="parentComment")
     * @orm\joinColumn(onDelete="CASCADE")
     */
    private $parentComment;
    /**
     * @return Comment[] Replies on this comment
     * @ORM\OneToMany(targetEntity="Comment", mappedBy="parentComment")
     */
    private $replies;

    public function __toString()
    {
        return '';//$this->getContent();
    }


    // ===================================== ===================


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->replies = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set content
     *
     * @param string $content
     *
     * @return Comment
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set datetime
     *
     * @param \DateTime $datetime
     *
     * @return Comment
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
     * Set request
     *
     * @param \AppBundle\Entity\Request $request
     *
     * @return Comment
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
     * Set author
     *
     * @param \AppBundle\Entity\User $author
     *
     * @return Comment
     */
    public function setAuthor(\AppBundle\Entity\User $author = null)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get author
     *
     * @return \AppBundle\Entity\User
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Set parentComment
     *
     * @param \AppBundle\Entity\comment $parentComment
     *
     * @return Comment
     */
    public function setParentComment(\AppBundle\Entity\comment $parentComment = null)
    {
        $this->parentComment = $parentComment;

        return $this;
    }

    /**
     * Get parentComment
     *
     * @return \AppBundle\Entity\comment
     */
    public function getParentComment()
    {
        return $this->parentComment;
    }

    /**
     * Add reply
     *
     * @param \AppBundle\Entity\Comment $reply
     *
     * @return Comment
     */
    public function addReply(\AppBundle\Entity\Comment $reply)
    {
        $this->replies[] = $reply;

        return $this;
    }

    /**
     * Remove reply
     *
     * @param \AppBundle\Entity\Comment $reply
     */
    public function removeReply(\AppBundle\Entity\Comment $reply)
    {
        $this->replies->removeElement($reply);
    }

    /**
     * Get replies
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getReplies()
    {
        return $this->replies;
    }
}
