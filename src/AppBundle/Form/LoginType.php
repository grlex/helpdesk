<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 05.09.2017
 * Time: 17:59
 */

namespace AppBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class LoginType extends AbstractType{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('login', TextType::class ,array('label'=>'login'));
        $builder->add('password',PasswordType::class, array('label'=>'password'));
        $builder->add('_remember_me',CheckboxType::class, array('mapped'=>false,
            'label'=>'remember_me',
            'required'=>false
        ));
        $builder->add('submit',SubmitType::class, array('label'=>'enter'));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection' => true,
            'csrf_token_id' => 'authenticate',
            'csrf_field_name' => '_csrf_token',
            'translation_domain'=> 'forms'
        ));
    }

}