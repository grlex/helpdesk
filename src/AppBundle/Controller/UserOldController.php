<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 06.09.2017
 * Time: 20:10
 */

namespace AppBundle\Controller;


use AppBundle\Form\UserNewEditType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\User;
use AppBundle\Form\RemoveFormType;
/**
 * Class UserController
 * @package AppBundle\Controller
 * @Security("is_granted('IS_AUTHENTICATED_REMEMBERED')")
 */
class UserOldController extends Controller {

    /**
     * @param int $page
     * @param int $pageSize
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(Request $request){
        $page = $request->query->get('page',1);
        $page = $page>0 ? $page : 1;
        $pageSize = $request->query->get('pageSize',10);
        $pageSize = $pageSize>0 ? $pageSize : 10;

        $usersCount = $this->getDoctrine()
            ->getManager()
            ->createQuery('select count(u) from AppBundle\Entity\User u')
            ->getSingleScalarResult();
        $totalPages = ceil($usersCount/$pageSize);

        $users = $this->get('doctrine')->getManager()->createQuery('select u from AppBundle\Entity\User u')
            ->setFirstResult(($page-1)*$pageSize)
            ->setMaxResults($pageSize)
            ->getResult();
        return $this->render('user/list.html.twig',array(
            'users'=>$users,
            'currentPage'=>$page,
            'totalPages'=>+$totalPages,
            'removeForm'=> $this->createForm(RemoveFormType::class)->createView()
        ));
    }
    /**
     * @param Request $request
     * Route("/user/new")
     */
    public function newAction(Request $request){
        $form = $this->createForm(UserNewEditType::class);
        $translator = $this->get('translator');
        $form->handleRequest($request);
        if($form->isSubmitted() && $form['password']->getData()!=$form['password-repeat']->getData()) {
            $form->get('password-repeat')
                ->addError(new FormError($translator->trans('new-user.passwords.must.be.equal', [], 'validators')));
            $form->get('password')
                ->addError(new FormError(''));
        }
        if($form->isSubmitted() && $form->isValid()){
            $user = $form->getData();
            $encoder = $this->get('security.password_encoder');
            $newPassword = $encoder->encodePassword($user,$user->getPassword());
            $user->setPassword($newPassword);
            $em = $this->get('doctrine')->getManager();
            $em->persist($user);
            $em->flush();
            return $this->redirect('/user/list');
        }
        return $this->render('user/new.html.twig', array('form'=>$form->createView()));
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, $id){

        $repository = $this->get('doctrine')->getRepository(User::class);
        $user = $repository->find($id);
        if(!$user) {
            throw $this->createNotFoundException('user.not.found');
        }
        $form = $this->createForm(UserNewEditType::class, $user, array('formUsage'=>'edit'));
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $user = $form->getData();
            $bcryptPasswordInfo = password_get_info($user->getPassword());
            if($bcryptPasswordInfo['algoName']!='bcrypt') {
                $encoder = $this->get('security.password_encoder');// should be bcrypt
                $newPassword = $encoder->encodePassword($user, $user->getPassword());
                $user->setPassword($newPassword);
            }
            $em = $this->get('doctrine')->getManager();
            //$em->persist($user);
            $em->flush();
            return $this->redirect('/user/list');
        }
        return $this->render('user/edit.html.twig', array('form'=>$form->createView()));
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function removeAction(Request $request, $id){

        $repository = $this->get('doctrine')->getRepository(User::class);
        $user = $repository->find($id);
        if(!$user){
            throw $this->createNotFoundException('user.not.found');
        }
        $form = $this->createForm(RemoveFormType::class,$user);
        $form->handleRequest($request);
        if($form->get('remove')->isClicked()) return $this->redirect('/user/list');
        if($form->isSubmitted()) {
            if ($this->getUser()->getId() == $user->getId()) {
                $token = $this->get('security.token_storage')->getToken();
            }
            $em = $this->get('doctrine')->getManager();
            $em->remove($user);
            $em->flush();
            return $this->redirect('/user/list');
        }
        return $this->render('user/remove.html.twig',array(
            'form'=>$form->createView(),
            'username'=>$user->getName()
        ));

    }
} 