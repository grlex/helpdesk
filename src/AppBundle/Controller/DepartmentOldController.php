<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 10.09.2017
 * Time: 23:04
 */

namespace AppBundle\Controller;

use AppBundle\Entity\Department;
use AppBundle\Form\DepartmentNewEditType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\User;
use AppBundle\Form\UserType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use AppBundle\Form\RemoveFormType;

class DepartmentOldController extends Controller {

    /**
     * @param int $page
     * @param int $pageSize
     * @return \Symfony\Component\HttpFoundation\Response
     * Route("/department/list")
     */
    public function listAction(Request $request){
        $page = intval($request->query->get('page',1));
        $page = $page>0 ? $page : 1;
        $pageSize = intval($request->query->get('pageSize',10));
        $pageSize = $pageSize>0 ? $pageSize : 10;

        $itemsCount = $this->getDoctrine()
            ->getManager()
            ->createQuery('select count(d) from AppBundle\Entity\Department d')
            ->getSingleScalarResult();
        $totalPages = ceil($itemsCount/$pageSize);
        $totalPages = $totalPages ?: 1;

        $items = $this->get('doctrine')->getManager()->createQuery('select d from AppBundle\Entity\Department d')
            ->setFirstResult(($page-1)*$pageSize)
            ->setMaxResults($pageSize)
            ->getResult();
        return $this->render('department/list.html.twig',array(
            'items'=>$items,
            'currentPage'=>$page,
            'totalPages'=>+$totalPages,
            'removeForm'=> $this->createForm(RemoveFormType::class)->createView()
        ));
    }
    /**
     * @param Request $request
     * Route("/department/new")
     * Security("has_role('ROLE_ADMIN')")
     */
    public function newAction(Request $request){
        $form = $this->createForm(DepartmentNewEditType::class);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){

            $em = $this->get('doctrine')->getManager();
            $em->persist($form->getData());
            $em->flush();
            return $this->redirect('/department/list');
        }
        return $this->render('department/new.html.twig', array('form'=>$form->createView()));
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * Route("/department/edit/{id}")
     * Security("has_role('ROLE_ADMIN')")
     */
    public function editAction(Request $request, $id){

        $repository = $this->get('doctrine')->getRepository(Department::class);
        $item = $repository->find($id);
        if(!$item) {
            throw $this->createNotFoundException();
        }
        $form = $this->createForm(DepartmentNewEditType::class, $item, array('formUsage'=>'edit'));
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){

            $em = $this->get('doctrine')->getManager();
            //$em->persist($item);
            $em->flush();
            return $this->redirect('/department/list');
        }
        return $this->render('department/edit.html.twig', array('form'=>$form->createView()));
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * Route("/department/remove/{id}")
     * Security("has_role('ROLE_ADMIN')")
     */
    public function removeAction(Request $request, $id){

        $repository = $this->get('doctrine')->getRepository(Department::class);
        $item = $repository->find($id);
        if(!$item){
            throw $this->createNotFoundException();
        }
        $form = $this->createForm(RemoveFormType::class,$item);
        $form->handleRequest($request);
        if($form->get('remove')->isClicked()) return $this->redirect('/department/list');
        if($form->isSubmitted()) {

            $em = $this->get('doctrine')->getManager();
            $em->remove($item);
            $em->flush();
            return $this->redirect('/department/list');
        }
        return $this->render('department/remove.html.twig',array(
            'form'=>$form->createView(),
            'itemName'=>$item->getName()
        ));

    }

} 