<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 08.09.2017
 * Time: 21:42
 */

namespace AppBundle\Form;


use AppBundle\Entity\Department;
use AppBundle\Entity\User;
use Proxies\__CG__\AppBundle\Entity\Role;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Validator\Constraints\Expression;

class UserNewEditType extends BaseNewEditType {

    private $translator;
    public function __construct(Translator $translator){
        $this->translator = $translator;
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name',TextType::class, array('label'=>'form.user.name'));
        $builder->add('login',TextType::class, array('label'=>'form.user.login'));
        //$builder->add('password',$passwordType, array('label'=>'form.user.password'));
        $this->addPasswordField($options['formUsage'],$builder);
        $builder->add('roles',EntityType::class, array(
            'label'=>'form.user.roles',
            'class'=>Role::class,
            'choice_label'=>'name',
            'choice_translation_domain'=>'messages',
            'multiple'=>true,
            'attr'=>array('data-placeholder'=>$this->translator->trans('form.user.choose-roles',[],'forms'))
        ));
        $builder->add('department',EntityType::class, array(
            'label'=>'form.user.department',
            'class'=>Department::class,
            'choice_label'=>'name'));
        // use twitter/typeahead.js  jQuery plugin for ajax-based autocomplete of existed positions
        $builder->add('position',TextType::class, array('label'=>'form.user.position',
                                                        'attr'=>array('class'=>'data-typeahead')));
        //$builder->add('submit',SubmitType::class, array('label'=>$submitLabel));
        $this->addButtons($options['formUsage'], $builder, '/user/list');
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefault('data_class', User::class);
    }

    private function addPasswordField($usage, FormBuilderInterface $builder){
        if($usage=='new'){
            $builder->add('password',PasswordType::class, array(
                'label'=>'form.user.password',
                'always_empty'=>false
            ));
            $builder->add('password_repeat',PasswordType::class, array(
                'label'=>'form.user.password-repeat',
                'mapped'=>false,
                'always_empty'=>false
            ));
        }
        else {
            $builder->add('password', TextType::class, array(
                'label' => 'form.user.password',
            ));
        }
    }

}