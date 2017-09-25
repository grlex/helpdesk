<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 12.09.2017
 * Time: 20:42
 */

namespace AppBundle\Controller;


use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Form\RemoveFormType;
use AppBundle\Entity\NamedEntityInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;


trait EntityControllerTrait {

    protected $entityClass;
    protected $newFormClass;
    protected $editFormClass;
    protected $viewBaseDir;
    public function initialize($entityClass, $newFormClass=null, $editFormClass=null, $viewBaseDir=null){
        $this->entityClass= $entityClass;
        $this->newFormClass = $newFormClass ?: preg_replace('/Entity/','Form',$entityClass).'NewType';
        $this->editFormClass = $editFormClass ?: preg_replace('/Entity/','Form',$entityClass).'EditType';
        $this->viewBaseDir = $viewBaseDir ?: substr(strtolower(strrchr($entityClass,'\\')),1);
        $reflection = new \ReflectionClass($entityClass);
        if(!$reflection->implementsInterface(NamedEntityInterface::class)){
            throw new InvalidArgumentException('Entity class MUST implement NamedEntityInterface');
        }
    }
    /**
     * @param int $page
     * @param int $pageSize
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(Request $request){


        $page = intval($request->query->get('page',1));
        $page = $page>0 ? $page : 1;
        $pageSize = intval($request->query->get('pageSize',10));
        $pageSize = $pageSize>0 ? $pageSize : 10;

        $queryBuilder = $this->get('doctrine')->getRepository($this->entityClass)->createQueryBuilder('entity');
        $this->onPreQueryList($queryBuilder);

        $entitiesCount = $queryBuilder->select('count(entity)')->getQuery()->getSingleScalarResult();
        $totalPages = ceil($entitiesCount/$pageSize);
        $totalPages = $totalPages ?: 1;

        $entities = $queryBuilder->select('entity')->getQuery()
            ->setFirstResult(($page-1)*$pageSize)
            ->setMaxResults($pageSize)
            ->getResult();

        $entities = $this->onPostQueryList($entities) ?: $entities;
        return $this->render(sprintf('/%s/list.html.twig',$this->viewBaseDir),array(
            'entities'=>$entities,
            'currentPage'=>$page,
            'totalPages'=>+$totalPages,
            'removeForm'=> $this->createForm(RemoveFormType::class)->createView()
        ));
    }
    /**
     * @param Request $request
     */
    public function newAction(Request $request){


        $options = array();
        if($this->editFormClass == $this->newFormClass) $options['formUsage']='new';
        $form = $this->createForm($this->newFormClass, null, $options);

        $form->handleRequest($request);
        $isValid = $this->onNewValidate($form)!==false;
        if($form->isSubmitted() && $form->isValid()  && $isValid){
            $entity = $form->getData();
            if($this->OnPreNewPersist($entity)!==false) {
                $em = $this->get('doctrine')->getManager();
                $em->persist($entity);
                $em->flush();
                $this->onPostNewPersist($entity);
            }
            return $this->redirect(sprintf('/%s/list',$this->viewBaseDir));
        }



        return $this->render(sprintf('/%s/new.html.twig',$this->viewBaseDir), array('form'=>$form->createView()));
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, $id){

        $repository = $this->get('doctrine')->getRepository($this->entityClass);
        $entity = $repository->find($id);
        if(!$entity) {
            throw $this->createNotFoundException();
        }

        $options = array();
        if($this->editFormClass == $this->newFormClass) $options['formUsage']='edit';
        $form = $this->createForm($this->editFormClass, $entity, $options);


        $form->handleRequest($request);


        $isValid = $this->onEditValidate($form)!==false;
        if($form->isSubmitted() && $form->isValid() && $isValid){
            if($this->onPreEditPersist($entity)!==false) {
                $em = $this->get('doctrine')->getManager();
                $em->flush();
                $this->onPostEditPersist($entity);
            }
            return $this->redirect(sprintf('/%s/list',$this->viewBaseDir));
        }
        return $this->render(sprintf('/%s/edit.html.twig',$this->viewBaseDir), array('form'=>$form->createView()));
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function removeAction(Request $request, $id){

        $repository = $this->get('doctrine')->getRepository($this->entityClass);
        $entity = $repository->find($id);
        if(!$entity){
            throw $this->createNotFoundException();
        }
        $form = $this->createForm(RemoveFormType::class,$entity);
        $form->handleRequest($request);
        if($form->get('remove')->isClicked()) return $this->redirect(sprintf('/%s/list',$this->viewBaseDir));
        $isValid = $this->onRemoveValidate($form)!==false;
        if($form->isSubmitted() && $isValid) {

            if(($redirect = $this->onPreRemove($entity))!==false) {
                $em = $this->get('doctrine')->getManager();
                $em->remove($entity);
                $em->flush();
                $this->onPostRemove($entity);
            }
            return $this->redirect($redirect ?: sprintf('/%s/list',$this->viewBaseDir));
        }
        return $this->render(sprintf('/%s/remove.html.twig',$this->viewBaseDir),array(
            'form'=>$form->createView(),
            'entityName'=>$entity->getName()
        ));

    }


    /**
     * @param QueryBuilder $builder use builder to change query statement
     */
    protected function onPreQueryList(QueryBuilder $builder){

    }
    /**
     * @param $result NamedEntityInterface[] Queried entities
     * @return array[] Array of entities those should be used as querying result
     */
    protected function onPostQueryList($result){
        return false;
    }
    /**
     * @param NamedEntityInterface $entity Entity to be persisted
     * @return bool return false to cancel persisting
     */
    protected function onPreNewPersist(NamedEntityInterface $entity){}
    /**
     * @param NamedEntityInterface $entity Entity to be persisted
     */
    protected function onPostNewPersist(NamedEntityInterface $entity){}
    /**
     * @param NamedEntityInterface $entity Entity to be persisted
     * @return bool return false to cancel persisting
     */
    protected function onPreEditPersist(NamedEntityInterface $entity){}
    /**
     * @param NamedEntityInterface $entity Entity to be persisted
     */
    protected function onPostEditPersist(NamedEntityInterface $entity){}

    /**
     * @param NamedEntityInterface $entity Entity to be removed
     * @return bool return false to cancel removing
     */
    protected function onPreRemove(NamedEntityInterface $entity){}

    /**
     * @param NamedEntityInterface $entity
     * @return string  uri to redirect after removing
     *
     */
    protected function onPostRemove(NamedEntityInterface $entity){}

    /**
     * @param  FormInterface $form Validated form
     * @return bool return false to force entity be invalid. Also you need manually add errors to the form.
     */
    protected function onNewValidate(FormInterface $form){
    }
    /**
     * @param  FormInterface $form Validated form
     * @return bool return false to force entity be invalid. Also you need manually add errors to the form.
     */
    protected function onEditValidate(FormInterface $form){
    }
    /**
     * @param  FormInterface $form Validated form
     * @return bool return false to force entity be invalid. Also you need manually add errors to the form.
     */
    protected function onRemoveValidate(FormInterface $form){
    }

} 