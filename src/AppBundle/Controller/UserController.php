<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 12.09.2017
 * Time: 21:22
 */

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Translation\TranslatorInterface;

class UserController extends CommonEntityController {


    protected function onPreQueryList(QueryBuilder $builder){
        $builder->andWhere('e.removed != 1');
    }

    protected function onPostQueryList(array &$users){
       foreach($users as &$user){
            $strRoles = [];
            foreach($user->getRoles() as $role) {
                $strRoles[] = $this->get('translator')->trans($role->getRole());
            }
           if($user == $this->getUser()) $user = clone $user; // deep = false

           $user->setTextRoles($strRoles);
        }
    }

    protected function onPreAdd( $user)
    {
        $encoder = $this->get('security.password_encoder');
        $newPassword = $encoder->encodePassword($user,$user->getPassword());
        $user->setPassword($newPassword);
    }

    protected function onPreEdit( $user)
    {
        $bcryptPasswordInfo = password_get_info($user->getPassword());
        if($bcryptPasswordInfo['algoName']!='bcrypt') {
            $encoder = $this->get('security.password_encoder');// should be bcrypt
            $newPassword = $encoder->encodePassword($user, $user->getPassword());
            $user->setPassword($newPassword);
        }
    }

    protected function onPreRemove( $user)
    {
            $user->setRemoved(true);
            $em = $this->get('doctrine')->getManager();
            $em->flush();
            return false;
    }

    protected function onNewValidate(FormInterface $form)
    {
        $translator = $this->get('translator');
        if($form['password']->getData()!=$form['password_repeat']->getData()) {
            $form->get('password_repeat')
                ->addError(new FormError($translator->trans('new-user.passwords.must.be.equal', [], 'validators')));
            $form->get('password')
                ->addError(new FormError(''));
            return false;
        }
    }

}