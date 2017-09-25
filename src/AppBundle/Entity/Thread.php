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

/**
 * Class Thread
 * @package AppBundle\Entity
 * @ORM\Entity
 * ORM\Table(name="thread")
 */
class Thread extends BaseThread  {

    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="string")
     *
     */
    protected $id;


}
