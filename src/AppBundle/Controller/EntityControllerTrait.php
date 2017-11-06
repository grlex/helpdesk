<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 12.09.2017
 * Time: 20:42
 */

namespace AppBundle\Controller;


use AppBundle\Entity\BaseEntity;
use AppBundle\EntityListFilter\EntityListFilter;
use AppBundle\EntityListFilter\FilterBuilder\AppFilterBuilder;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Form\Extension\Core\Type;

use AppBundle\Form\EntityFilterType;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

trait EntityControllerTrait {

    protected $entityClass;
    protected $formClass;
    protected $viewBaseDir;
    protected $entityName;

    public function initialize($entityClass){
        $this->entityClass= $entityClass;
        $this->formClass =  preg_replace('/Entity/','Form',$entityClass).'Type';
        $this->entityName = substr(strtolower(strrchr($entityClass,'\\')),1);
        $this->viewBaseDir = $this->entityName;

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



        $filterForm = $this->createFilterForm();
        $filterForm->handleRequest($request);


        $entityAlias = 'e';
        $entityMetadata = $this->get('doctrine')->getEntityManager()->getClassMetadata($this->entityClass);

        $queryBuilder = $this->get('doctrine')->getRepository($this->entityClass)->createQueryBuilder($entityAlias);


        $filter = new EntityListFilter($filterForm->getData(), $entityMetadata);
        $filter->setBuilder(new AppFilterBuilder($this->get('translator'), $this->get('doctrine'), $entityAlias));
        $filter->applyFilter($queryBuilder);

        //dump($request->query, $filterForm->getData(), $queryBuilder->getDql());die();


        $this->onPreQueryList($queryBuilder);


        $entitiesCount = $queryBuilder->select(sprintf('count(%s)',$entityAlias))->getQuery()->getSingleScalarResult();
        $totalPages = ceil($entitiesCount/$pageSize);
        $totalPages = $totalPages ?: 1;

        if($page>$totalPages){
            $request->query->replace( ['page' => $totalPages] );
            $queryParams = $request->query->all();
            $uri = sprintf('%s?%s',$request->getPathInfo(), http_build_query($queryParams));
            return $this->redirect($uri);
        }

        //dump($totalPages, $queryBuilder->getQuery()->getSql());

        $queryBuilder->setFirstResult(($page-1)*$pageSize)->setMaxResults($pageSize);
        $entities = $queryBuilder->select($entityAlias)->getQuery()->getResult();

        //dump($filterForm->getData(), $queryBuilder->getDql(), $entities);//die();

        $this->onPostQueryList($entities);

        $this->checkAccess('list', $entities );


        $viewName = 'list';//$request->query->has('table-only') ? 'list_table' : 'list';

        $viewVars = array(
            'entities'=>$entities,
            'currentPage'=>$page,
            'totalPages'=>intval($totalPages),
            'filterForm'=> $filterForm->createView(),
            'fields'=> call_user_func([$this->entityClass, 'getFields']),
            'entityName'=> $this->entityName,
            'canActCallback' => function($action, $items){
                $items = is_array($items) ? $items : [ $items ];
                return $this->canAct($action, $items );
            }
        );

        return $this->getResponse($viewName, $viewVars);

    }
    /**
     * @param Request $request
     */
    public function newAction(Request $request){

        $form = $this->createNewForm();

        $form->handleRequest($request);

        $this->checkAccess('new', [$form->getData()] );

        $isValid = $this->onNewValidate($form)!==false;
        if($form->isSubmitted() && $form->isValid()  && $isValid){
            $entity = $form->getData();
            if($this->OnPreAdd($entity)!==false) {
                $em = $this->get('doctrine')->getManager();
                $em->persist($entity);
                $em->flush();
                $this->onPostAdd($entity);
            }

            return $this->redirect($form['_back_uri']->getData());
        }



        return $this->getResponse('form', array('form'=>$form->createView()));
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


        $form = $this->createEditForm($entity);
        $form->handleRequest($request);

        $this->checkAccess('edit', [$form->getData()] );


        $isValid = $this->onEditValidate($form)!==false;
        if($form->isSubmitted() && $form->isValid() && $isValid){
            //dump($form['user']->getConfig()->getOptions());die();
            if($this->onPreEdit($entity)!==false) {
                $em = $this->get('doctrine')->getManager();
                $em->flush();
                $this->onPostEdit($entity);
            }

            return $this->redirect($form['_back_uri']->getData());
        }
        return $this->getResponse( 'form', array('form'=>$form->createView()));
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function removeAction(Request $request, $id){

        $repository = $this->get('doctrine')->getRepository($this->entityClass);
        $em = $this->get('doctrine')->getManager();

        $ids = is_integer($id) ? [$id] : mb_split('&',$id);
        $entitiesToRemove = $em->createQuery(sprintf('select e from %s e where e.id in (%s)',$this->entityClass, join(',',$ids)))
                               ->getResult();
        $groupAction = count($ids) > 1;

        $form = $this->createRemoveForm();
        $form->handleRequest($request);

       $this->checkAccess('remove', $entitiesToRemove);

        $isValid = $this->onRemoveValidate($form)!==false;

        if($form->isSubmitted() && $isValid) {

            foreach($entitiesToRemove as $key=>$entity){
                if ($this->onPreRemove($entity) !== false) {
                    $em->remove($entity);
                }
                else{
                    unset($entitiesToRemove[$key]);
                }
            }
            $em->flush();

            foreach($entitiesToRemove as $entity){
               $this->onPostRemove($entity);
            }
            return $this->redirect($form['_back_uri']->getData());
        }
        $title = $groupAction
            ? $this->get('translator')->trans(sprintf('remove-group.%s.confirmation', $this->entityName),[],'forms')
            : $this->get('translator')->trans(sprintf('remove.%s.confirmation.%%item%%.%%id%%', $this->entityName), [
                '%item%'=>$entitiesToRemove[0]->__toString(),
                '%id%'=>$entitiesToRemove[0]->getId()], 'forms');
        return $this->getResponse( 'form', array(
            'form'=>$form->createView(),
            'title'=>$title,
            'entities'=>$entitiesToRemove
        ));

    }


    protected function getResponse($viewName, array  $viewVars = []){

        $request = $this->get('request_stack')->getCurrentRequest();

        $format = $request->getRequestFormat();

        $view = sprintf('%s/%s.%s.twig',$this->viewBaseDir, $viewName, $format);
        if(!$this->get('templating')->exists($view)){
            $view = null;
        }

        if($view){
            $this->amendViewVariables($view, $viewVars);
            return $this->render($view, $viewVars);
        }

        return new Response('no representation', Response::HTTP_NOT_ACCEPTABLE);
    }

    /**
     * @param string[] $data phrases to filter entities
     * @param array $options
     * @return mixed
     */
    private function createFilterForm(array $data=[], $options=[]){
        $options['method'] = 'get';
        $options['target_entity'] = $this->entityClass;
        return  $this->get('form.factory')->createNamed('filter', EntityFilterType::class, $data, $options);

    }
    protected function createNewForm($entity=null, array $options=[]){
        return $this->createEntityForm($this->formClass, $entity, 'new', $options);
    }
    protected function createEditForm($entity=null, $options=[]){
        return $this->createEntityForm($this->formClass, $entity, 'edit', $options);
    }
    protected function createRemoveForm($entity=null, $options=[]){
        return $this->createEntityForm($this->formClass, $entity, 'remove', $options);
    }
    protected function createEntityForm($formClass, $entity, $usage, array $options = []){
        $httpRequest = $this->get('request_stack')->getCurrentRequest();

        $back_uri = $httpRequest->query->get('back_uri') ?: '';
        $back_uri = $back_uri ?: $httpRequest->headers->get('referer');

        if(empty($back_uri) or parse_url($back_uri)['host']!=$httpRequest->getHost()){
            $back_uri = sprintf('/%s/list',$this->entityName);
        }

        $options['form_usage'] = $usage;
        $options['cancel_uri'] = $back_uri;
        $form =  $this->createForm($formClass, $entity, $options);
        $form['_back_uri']->setData($back_uri);
        return $form;
    }


    protected function amendViewVariables($viewName, array &$variables){
    }

    protected function checkAccess($action, array $entities){
        if(!$this->canAct($action, $entities)){
            throw new AccessDeniedException();
        }
    }
    protected function canAct($action, array $entities){
        return true;
    }



    /**
     * @param QueryBuilder $builder use builder to change query statement
     */
    protected function onPreQueryList(QueryBuilder $builder){
    }
    /**
     * @param $result BaseEntity[] Queried entities
     */
    protected function onPostQueryList(array &$entities){
    }
    /**
     * @param BaseEntity $entity Entity to be persisted
     * @return bool return false to cancel persisting
     */
    protected function onPreAdd( $entity){}
    /**
     * @param BaseEntity $entity Entity to be persisted
     */
    protected function onPostAdd( $entity){}
    /**
     * @param BaseEntity $entity Entity to be persisted
     * @return bool return false to cancel persisting
     */
    protected function onPreEdit( $entity){}
    /**
     * @param BaseEntity $entity Entity to be persisted
     */
    protected function onPostEdit( $entity){}

    /**
     * @param BaseEntity $entity Entity to be removed
     * @return bool return false to cancel removing
     */
    protected function onPreRemove( $entity){}

    /**
     * @param BaseEntity $entity
     */
    protected function onPostRemove( $entity){}

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