<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 14.09.2017
 * Time: 17:14
 */

namespace AppBundle\Controller;

use AppBundle\Entity\Comment;
use AppBundle\Entity\LifecycleStep;
use AppBundle\Entity\Thread;
use AppBundle\Form\LifecycleStepType;
use AppBundle\Form\RequestType;
use AppBundle\Service\HtmlImagesExtractor;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Entity\Request;
use Symfony\Component\HttpFoundation\Request as HttpRequest;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;

class RequestController extends CommonEntityController {
    //use EntityControllerTrait;

    private $imagesExtractor;
    private $onEditRequestStatusChanged = false;
    public function __construct(RequestStack $requestStack, TranslatorInterface $translator, HtmlImagesExtractor $imagesExtractor){
        parent::__construct($requestStack);

        $statuses = &Request::getStatuses();
        foreach($statuses as &$textStatus)
            $textStatus = $translator->trans('request.status.'.$textStatus,[],'entities');
        $priorities = &Request::getPriorities();
        foreach($priorities as &$textPriority)
            $textPriority = $translator->trans('request.priority.'.$textPriority,[],'entities');

        $this->imagesExtractor = $imagesExtractor;

        //$this->initialize(Request::class);

    }

    protected function onPreAdd( $request){

        $lifecycleStep = new LifecycleStep();
        $lifecycleStep->setRequestStatus(Request::STATUS_OPENED);
        $lifecycleStep->setUser($this->getUser());
        $lifecycleStep->setDatetime(new \DateTime('now'));
        $request->setStatus(Request::STATUS_OPENED);
        $request->setUser($this->getUser());
        $request->addLifecycleStep($lifecycleStep);
        $this->onPreSave($request);
    }
    protected function onPostAdd( $request){
        $commentThread  = new Thread();
        $commentThread->setId('request_'.$request->getId());
        $commentThread->setPermalink('/request/view/'.$request->getId());
        $commentThread->setNumComments(0);
        $commentThread->setCommentable(true);
        $request->setThread($commentThread);
        $em = $this->get('doctrine')->getEntityManager();
        $em->persist($commentThread);
        $em->flush();
    }
    protected function onPreEdit( $request){
        $em = $this->get('doctrine')->getEntityManager();
        $originalRequestData = $em->getUnitOfWork()->getOriginalEntityData( $request );

        $this->onPreSave($request);
        $this->onEditRequestStatusChanged = $originalRequestData['status'] != $request->getStatus();
    }
    public function onPostEdit($request){
        if($this->onEditRequestStatusChanged){
            $em = $this->get('doctrine')->getEntityManager();
            $step = new LifecycleStep();
            $step->setUser($this->getUser()); // admin
            $step->setRequestStatus($request->getStatus());
            $step->setRequest($request);
            $step->setDatetime(new \DateTime('now'));

            $comment = new Comment();
            $comment->setAuthor($this->getUser());
            $comment->setThread($request->getThread());
            $commentBody = sprintf("<p>%s (%s)</br>%s</p>",
                $this->get('translator')->trans('request.status.changed-by-admin',[], 'entities'),
                Request::getStatuses()[$request->getStatus()],
                empty($data['executor']) ? '' : sprintf('<br/>%s: %s', $this->get('translator')->trans('request.executor',[],'entities'), $data['executor'])
            );
            $comment->setBody($commentBody);
            $this->get('fos_comment.custorm_manager.comment')->saveComment($comment);
            $em->persist($request->getThread());
            $step->setComment($comment);
            $request->addLifecycleStep($step);
            $em->persist($step);
            $em->flush();
        }
    }
    private function onPreSave(Request $request){
        $request->setDescription($this->get('custom_html_purifier')->parse($request->getDescription()));
        foreach($request->getFiles() as $file){
            $file->setConfirmed(1);
        }
        //old files will be deleted automatically due to "orphanRemoval"

        $em = $this->get('doctrine')->getEntityManager();
        $originalDescription = $em->getUnitOfWork()->isInIdentityMap($request)
                                ? $em->getUnitOfWork()->getOriginalEntityData( $request )['description']
                                : '';
        // old and new description images
        $oldImages = $this->imagesExtractor->extract($originalDescription);
        $newImages = $this->imagesExtractor->extract($request->getDescription());

        foreach($newImages as $newImage){
            $newImage->setConfirmed(1);
            $em->persist($newImage);
            foreach($oldImages as $key=>$oldImage){
                if ($oldImage->getId() == $newImage->getId()) {
                    unset($oldImages[$key]);
                    break;
                }
            }
        }
        foreach($oldImages as $oldImage) {
            $em->remove($oldImage);
        }
    }

    protected function onPostRemove($request){
        $em = $this->get('doctrine')->getEntityManager();
        $descriptionImages = $this->imagesExtractor->extract($request->getDescription());
        foreach($descriptionImages as $image) $em->remove($image);
        $em->flush();
    }


    protected function amendViewVariables($viewName, array &$variables){
        switch($viewName){
            case 'request/list.html.twig':
                $parentRequest = $this->get('request_stack')->getParentRequest();
                if(!$parentRequest) break;
                switch($parentRequest->getPathInfo()){
                    case '/request/my-list':
                        $variables['title'] = $this->get('translator')->trans('items.request.my-list-title');
                        break;
                    case '/request/distribute-list':
                        $variables['title'] = $this->get('translator')->trans('items.request.distribute-list-title');
                        break;
                    case '/request/process-list':
                        $variables['title'] = $this->get('translator')->trans('items.request.process-list-title');
                        break;
                    case '/request/close-list':
                        $variables['title'] = $this->get('translator')->trans('items.request.close-list-title');
                        break;
                }
                break;
        }
    }



    /**
     * @param $id
     * @Route("/request/view/{id}")
     * @Method({"GET"})
     */
    public function viewAction($id, HttpRequest $request){
        $request = $this->get('doctrine')->getRepository('AppBundle:Request')->find($id);

        $this->checkAccess('view', [$request]);
        //return $this->render('request/view.html.twig', array('request'=>$request));
        return $this->getResponse('view', array('request'=>$request));
    }

    /**
     * @param $id
     * @param HttpRequest $request
     * @Route("/request/{action}/{id}", requirements={"action": "reject|reopen|distribute|process|accept|discard|close"})
     */
    public function statusAction($id, $action, HttpRequest $httpRequest){

        $em = $this->get('doctrine')->getManager();

        $ids = is_integer($id) ? [$id] : mb_split('&',$id);
        $requests = $em->createQuery(sprintf('select e from %s e where e.id in (%s)',Request::class, join(',',$ids)))
            ->getResult();

        $this->checkAccess($action, $requests);

        $groupAction = count($ids)>1;


        $form = $this->createEntityForm(LifecycleStepType::class, null, $action);
        $form->handleRequest($httpRequest);


        if($form->isSubmitted()) {

            $status = Request::STATUS_OPENED;
            switch($action){
                case 'reopen':
                    $status = Request::STATUS_OPENED;
                    break;
                case 'reject':
                    $status = Request::STATUS_REJECTED;
                    break;
                case 'distribute':
                    $status = Request::STATUS_DISTRIBUTED;
                    break;
                case 'process':
                    $status = Request::STATUS_PROCESSED;
                    break;
                case 'accept':
                    $status = Request::STATUS_ACCEPTED;
                    break;
                case 'discard':
                    $status = Request::STATUS_DISCARDED;
                    break;
                case 'close':
                    $status = Request::STATUS_CLOSED;
                    break;
            }

            $data = $form->getData();
            foreach($requests as $request){
                $request->setStatus($status);
                $step = new LifecycleStep();
                $step->setUser($this->getUser());
                $step->setRequestStatus($status);
                //$step->setRequest($request); in Request::addLifecycleStep()
                $step->setDatetime(new \DateTime('now'));

                if(!empty($data['comment'])) {
                    $comment = new Comment();
                    /*$commentBody = $this->get('translator')->trans('request.status.changed_to_%status%', ['%status%' => Request::getStatuses()[$status]], 'entities');
                    $commentBody = sprintf("<p>%s%s</p><br/>%s",
                        $commentBody,
                        empty($data['executor']) ? '' : sprintf('<br/>%s: %s', $this->get('translator')->trans('request.executor', [], 'entities'), $data['executor']),
                        $data['comment']//empty($data['comment']) ? '' : $data['comment']
                    );*/
                    $comment->setBody($data['comment']);
                    $comment->setAuthor($this->getUser());
                    $comment->setThread($request->getThread());
                    //$comment->setBody($this->get('custom_html_purifier')->parse($data['comment']));
                    $this->get('fos_comment.custorm_manager.comment')->saveComment($comment);
                    //$em->persist($request->getThread());
                    $step->setComment($comment);
                }

                if(array_key_exists('executor',$data)) {
                    $request->setExecutor($data['executor']);
                }
                $request->addLifecycleStep($step);
                //$em->persist($step);
                $em->persist($request);
            }
            $em->flush();

            return $this->redirect($form['_back_uri']->getData());

        }
        $title = $groupAction
            ? $this->get('translator')->trans(sprintf('%s-group.%s.confirmation',$action,$this->entityName),[],'forms')
            : $this->get('translator')->trans(sprintf('%s.%s.confirmation.%%item%%.%%id%%',$action,$this->entityName), [
                    '%item%'=>$requests[0]->__toString(),
                    '%id%'=>$requests[0]->getId()], 'forms');
        return $this->getResponse( 'form', array(
            'form'=>$form->createView(),
            'entities'=> $requests,
            'title'=> $title
        ));


    }

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @Route("/request/my-list")
     * @Security("has_role('ROLE_USER')")
     */
    public function myAction(HttpRequest $httpRequest){
        $query = $httpRequest->query->all();
        $query['filter']['user'] = $this->getUser()->getLogin();
        return $this->forward('AppBundle:Request:list', [], $query);
    }
    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @Route("/request/process-list")
     * @Security("has_role('ROLE_EXECUTOR')")
     */
    public function processRequestListAction(HttpRequest $httpRequest){
        $query = $httpRequest->query->all();
        $query['filter']['executor'] = $this->getUser()->getLogin();
        $query['filter']['status'] = Request::STATUS_DISTRIBUTED;

        return $this->forward('AppBundle:Request:list', [], $query);
    }
    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @Route("/request/distribute-list")
     * @Security("has_role('ROLE_MODERATOR')")
     */
    public function distributeRequestListAction(HttpRequest $httpRequest){
        $query = $httpRequest->query->all();

        $query['filter']['status'] = sprintf('%s|%s', Request::STATUS_OPENED, Request::STATUS_DISCARDED);

        return $this->forward('AppBundle:Request:list', [], $query);
    }
    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @Route("/request/close-list")
     * @Security("has_role('ROLE_MODERATOR')")
     */
    public function closeRequestListAction(HttpRequest $httpRequest){
        $query = $httpRequest->query->all();

        $query['filter']['status'] = sprintf('%s|%s', Request::STATUS_ACCEPTED, Request::STATUS_DISCARDED);

        return $this->forward('AppBundle:Request:list', [], $query);
    }

    protected function canAct($action, array $requests){
        if($action=='new') return true;
        foreach($requests as $request){
            if(!$this->isGranted($action,$request)) // AppBundle\Security\RequestEntityVoter
                return false;
        }
        return true;
    }

    protected function createEditForm($entity=null, $options=[]){
        $usage = $this->getUser()->hasRole('ROLE_ADMIN') ? 'edit-admin' : 'edit';
        return $this->createEntityForm($this->formClass, $entity, $usage, $options);
    }


}