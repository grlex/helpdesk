<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 16.09.2017
 * Time: 16:49
 */

namespace AppBundle\Entity;

use FOS\CommentBundle\Entity\Thread as BaseThread;
use Doctrine\ORM\Mapping as ORM;
use FOS\CommentBundle\Model\RawCommentInterface;
use FOS\CommentBundle\Model\SignedCommentInterface;
use AppBundle\Entity\Request;

/**
 * Class Thread
 * @package AppBundle\Entity
 * @ORM\Entity
 * @ORM\Table(name="thread")
 *
 */
class Thread extends BaseThread  {

    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="string")
     *
     */
    protected $id;

    /**
     * @var Comment $comment
     * @ORM\OneToMany(targetEntity="Comment", mappedBy="thread", cascade={"persist", "remove"})
     */
    protected $comments;

    /**
     * @var Request $request
     * @ORM\OneToOne(targetEntity="Request", inversedBy="thread")
     */
    protected $request;

    /**
     * Set request
     *
     * @param \AppBundle\Entity\Request $request
     *
     * @return Request
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

}
