<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 12.09.2017
 * Time: 21:22
 */

namespace AppBundle\Controller;

use AppBundle\Entity\NamedEntityInterface;
use AppBundle\Entity\User;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use AppBundle\Form\UserNewEditType;

class UserController extends Controller {
    use EntityControllerTrait;
    public function __construct(){
        $this->initialize(User::class, UserNewEditType::class, UserNewEditType::class);
    }


    public function onPreQueryList(QueryBuilder $builder){
        $builder->where('entity.removed != 1');
    }
    public function onPreNewPersist(NamedEntityInterface $user)
    {
        $encoder = $this->get('security.password_encoder');
        $newPassword = $encoder->encodePassword($user,$user->getPassword());
        $user->setPassword($newPassword);
    }

    public function onPreEditPersist(NamedEntityInterface $user)
    {
        $bcryptPasswordInfo = password_get_info($user->getPassword());
        if($bcryptPasswordInfo['algoName']!='bcrypt') {
            $encoder = $this->get('security.password_encoder');// should be bcrypt
            $newPassword = $encoder->encodePassword($user, $user->getPassword());
            $user->setPassword($newPassword);
        }
    }

    public function onPreRemove(NamedEntityInterface $user)
    {
        if ($this->getUser()->getId() == $user->getId()) {
            setcookie('PHPSESSID', false, 0, '/');
            // will be removed in EntityControllerTrait::removeAction
            return "/";
        }
        else{
            $user->setRemoved(true);
            $em = $this->get('doctrine')->getManager();
            $em->flush();
            return false;
        }


    }

    public function onNewValidate(FormInterface $form)
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

    public function onEditValidate(FormInterface $form){}

    public function onRemoveValidate(FormInterface $form){}
}