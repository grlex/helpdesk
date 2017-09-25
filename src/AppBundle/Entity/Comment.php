<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 15.09.2017
 * Time: 10:49
 */

namespace AppBundle\Entity;
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
     * @ORM\OneToOne(targetEntity="User")
     * @ORM\JoinColumn(onDelete="SET NULL", nullable=true)
     */
    protected $author;

    /**
     * @var BaseThread
     * @ORM\ManyToOne(targetEntity="Thread")
     */
    protected $thread;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    protected $rawBody;

    /**
     * @var File[] Файлы, прикрепленные к заявке
     * @ORM\ManyToMany(targetEntity="File", orphanRemoval=true)
     * @ORM\JoinTable(name="comment_file",
     *      joinColumns = { @ORM\JoinColumn(name="comment_id", referencedColumnName="id", onDelete="CASCADE") },
     *      inverseJoinColumns = { @ORM\JoinColumn(name="file_id", referencedColumnName="id", onDelete="CASCADE") } )
     */
    protected $files;


    public function __toString()
    {
        return $this->getBody();
    }


    // ===================================== ===================




    /**
     * Constructor
     */
    public function __construct()
    {
        $this->files = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @param UserInterfacer $author
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
     * @return \AppBundle\Entity\User
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

    /**
     * Add file
     *
     * @param \AppBundle\Entity\File $file
     *
     * @return Comment
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
}
