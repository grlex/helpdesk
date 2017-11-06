<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 27.09.2017
 * Time: 10:19
 */

namespace AppBundle\FOSComment;

use FOS\CommentBundle\Model\CommentInterface;
use FOS\CommentBundle\Model\ThreadInterface;
use FOS\RestBundle\View\View;
use Symfony\Bundle\FrameworkBundle\Templating\TemplateReference;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ModifiedThreadController extends Controller {


    /**
     * Get a comment of a thread.
     *
     * @param string $id        Id of the thread
     * @param mixed  $commentId Id of the comment
     *
     * @return View
     */
    public function getThreadCommentAction($id, $commentId)
    {


        $thread = $this->container->get('fos_comment.manager.thread')->findThreadById($id);
        //$comment = $this->container->get('fos_comment.manager.comment')->findCommentById($commentId);

        /* change */
        $commentInfo = $this->container->get('fos_comment.manager.comment')->findCommentTreeByCommentId($commentId);
        $commentInfo = count($commentInfo)>0 ? $commentInfo[0] : ['comment'=>null, 'children'=>null];
        $comment = $commentInfo['comment'];
        $children = $commentInfo['children'];
        /*end change */

        $parent = null;

        if (null === $thread || null === $comment || $comment->getThread() !== $thread) {
            throw new NotFoundHttpException(sprintf("No comment with id '%s' found for thread with id '%s'", $commentId, $id));
        }

        $ancestors = $comment->getAncestors();
        if (count($ancestors) > 0) {
            $parent = $this->getValidCommentParent($thread, $ancestors[count($ancestors) - 1]);
        }

        $view = View::create()
            ->setData(array('comment' => $comment, 'children' => $children, 'thread' => $thread, 'parent' => $parent, 'depth' => $comment->getDepth()))
            ->setTemplate(new TemplateReference('FOSCommentBundle', 'Thread', 'comment'));

        return $this->getViewHandler()->handle($view);
    }



    /**
     * Checks if a comment belongs to a thread. Returns the comment if it does.
     *
     * @param ThreadInterface $thread    Thread object
     * @param mixed           $commentId Id of the comment.
     *
     * @return CommentInterface|null The comment.
     */
    private function getValidCommentParent(ThreadInterface $thread, $commentId)
    {
        if (null !== $commentId) {
            $comment = $this->container->get('fos_comment.manager.comment')->findCommentById($commentId);
            if (!$comment) {
                throw new NotFoundHttpException(sprintf('Parent comment with identifier "%s" does not exist', $commentId));
            }

            if ($comment->getThread() !== $thread) {
                throw new NotFoundHttpException('Parent comment is not a comment of the given thread.');
            }

            return $comment;
        }
    }

    /**
     * @return \FOS\RestBundle\View\ViewHandler
     */
    private function getViewHandler()
    {
        return $this->container->get('fos_rest.view_handler');
    }
} 