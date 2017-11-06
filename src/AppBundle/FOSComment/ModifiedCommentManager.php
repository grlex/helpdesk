<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 27.09.2017
 * Time: 10:20
 */

namespace AppBundle\FOSComment;


use AppBundle\Entity\Comment;
use FOS\CommentBundle\Entity\CommentManager;
use Doctrine\ORM\EntityManager ;
use FOS\CommentBundle\Sorting\SortingFactory;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ModifiedCommentManager extends CommentManager{

    public function __construct(EventDispatcherInterface $dispatcher, SortingFactory $factory, EntityManager $em, $class)
    {
        parent::__construct($dispatcher, $factory, $em, $class);
    }
    /**
     * {@inheritdoc}
     */
    public function findCommentTreeByCommentId($commentId, $sorter = null)
    {
        $qb = $this->repository->createQueryBuilder('c');
        /*$qb->join('c.thread', 't')
           ->where('LOCATE(:path, CONCAT(\'/\', CONCAT(c.ancestors, \'/\'))) > 0')
           ->orderBy('c.ancestors', 'ASC')
           ->setParameter('path', "/{$commentId}/");*/

        $qb->join('c.thread', 't')
            ->where('LOCATE(:id, c.ancestors ) > 0 or c.id=:id')
            ->orderBy('c.ancestors', 'ASC')
            ->setParameter('id', $commentId); // change

        $comments = $qb->getQuery()->execute();

        if (!$comments) {
            return array();
        }

        $sorter = $this->sortingFactory->getSorter($sorter);

        $trimParents = current($comments)->getAncestors();

        return $this->organiseComments($comments, $sorter, $trimParents);
    }

}