<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 15.09.2017
 * Time: 11:42
 */

namespace AppBundle\Form;


use AppBundle\Form\Transformer\FileCollectionTransformer;
use AppBundle\Form\Transformer\FileTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type;
use AppBundle\Entity\Request;
use AppBundle\Entity\Active;
use AppBundle\Entity\Category;
use AppBundle\Entity\User;
use AppBundle\Entity\Comment;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class RequestNewType extends AbstractType{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', Type\TextType::class, array('label'=>'form.request.name'));
        $builder->add('description', Type\TextAreaType::class, array(
            'label'=>'form.request.description',
            'attr'=>array('style'=>'resize:vertical;')
        ));
        $builder->add('priority', Type\ChoiceType::class, array(
            'label'=>'form.request.priority',
            'choices'=>array(
                'form.request.priority.critical'=>Request::PRIORITY_CRITICAL,
                'form.request.priority.high'=>Request::PRIORITY_HIGH,
                'form.request.priority.medium'=>Request::PRIORITY_MEDIUM,
                'form.request.priority.low'=>Request::PRIORITY_LOW
            )
        ));
        $builder->add('active', EntityType::class, array(
            'label'=>'form.request.active',
            'class'=>Active::class,
            'choice_label'=>'cabNumber',
            'group_by'=>function($val, $key, $index){
                return $val->getDepartment()->getName();
            }
        ));
        $builder->add('files', Type\CollectionType::class, array(
            'allow_add'=>true,
            'allow_delete'=>true,
            'entry_type'=>\AppBundle\Form\FileType::class,
            'label'=>'form.request.files',
            'required'=>false,
            'by_reference'=>false,
            'prototype'=>false
        ));


        $builder->add('category', EntityType::class, array(
            'label'=>'form.request.category',
            'class'=>Category::class,
            'choice_label'=>'name'
        ));
        $builder->add('user', EntityType::class, array(
            'label'=>'form.request.user',
            'class'=>User::class,
            'choice_label'=>function($val, $key, $index){
                return sprintf('%s (%s)',$val->getName(), $val->getLogin());
            }
        ));
        $builder->add('executor', EntityType::class, array(
            'label'=>'form.request.executor',
            'class'=>User::class,
            'choice_label'=>function($val, $key, $index){
                return sprintf('%s (%s)',$val->getName(), $val->getLogin());
            }
        ));
        $builder->add('cancel', Type\ButtonType::class,array(
            'label'=>'form.common.cancel'
        ));
        $builder->add('save', Type\SubmitType::class,array(
            'label'=>'form.common.save'
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver); // TODO: Change the autogenerated stub
        $resolver->setDefaults(array(
            'data_class'=>Request::class,
            'translation_domain' => 'forms',
        ));
    }
}