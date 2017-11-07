<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 20.09.2017
 * Time: 21:29
 */

namespace AppBundle\Form;


use AppBundle\Entity\BaseEntity;
use AppBundle\Entity\Role;
use AppBundle\Form\Transformer\FileTransformer;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\DBAL\Types\TextType;
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
use Symfony\Component\Translation\TranslatorInterface;

class RequestType extends BaseEntityType{

    protected $doctrine;
    public function __construct(Registry $doctrine, TranslatorInterface $translator){
        $this->doctrine = $doctrine;
        parent::__construct($translator);
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);


        $this->add('name', Type\TextType::class, array('label'=>'request.name'));

        $this->add('description', Type\TextAreaType::class, array(
            'label'=>'request.description',
            'attr'=>array('style'=>'resize:vertical;')
        ));

        $this->add('priority', Type\ChoiceType::class, array(
            'label'=>'request.priority',
            'choices'=>array_flip(Request::getPriorities())
        ));

        $this->add('status', Type\ChoiceType::class, array(
            'label'=>'request.status',
            'choices'=>array_flip(Request::getStatuses())
        ));

        $this->add('active', EntityType::class, array(
            'label'=>'request.active',
            'class'=>Active::class,
            'choice_label'=>'cabNumber',
            'group_by'=>function($val, $key, $index){
                return $val->getDepartment()->getName();
            }
        ));

        $this->add('files', FileCollectionType::class, array(
            'allow_add'=>true,
            'allow_delete'=>true,
            'entry_type'=>\AppBundle\Form\FileType::class,
            'label'=>'request.files',
            'add_label'=>'request.files.add',
            'required'=>false,
            'by_reference'=>false,
            'prototype'=>false,
            'translation_domain'=>'entities'
        ));


        $this->add('category', EntityType::class, array(
            'label'=>'request.category',
            'class'=>Category::class,
            'choice_label'=>'name'
        ));

        $this->add('user', EntityType::class, array(
            'label'=>'request.user',
            'class'=>User::class,
        ));

        $this->add('executor', EntityType::class, array(
            'required'=>false,
            'label'=>'request.executor',
            'class'=>User::class,
            'query_builder'=> $this->doctrine->getRepository('AppBundle:User')
                ->createQueryBuilder('u')
                ->where(sprintf("BIT_AND(u.rolesMask, %s) <> 0", Role::getMaskBits()[Role::ROLE_EXECUTOR]))
        ));

        $this->addButtons();

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefault('data_class',Request::class);
    }


    protected function isFieldUsed($name){
        switch($name){
            case 'name': return in_array($this->usage, ['new', 'edit', 'edit-admin' ]);
            case 'description': return in_array($this->usage, ['new', 'edit', 'edit-admin' ]);
            case 'priority': return in_array($this->usage, ['new', 'edit', 'edit-admin' ]);
            case 'status': return in_array($this->usage, [ 'edit-admin' ]);
            case 'active': return in_array($this->usage, ['new', 'edit', 'edit-admin' ]);
            case 'files': return in_array($this->usage, ['new', 'edit', 'edit-admin' ]);
            case 'category': return in_array($this->usage, ['new', 'edit', 'edit-admin' ]);
            case 'user': return in_array($this->usage, [ 'edit-admin' ]);
            case 'executor': return in_array($this->usage, [ 'edit-admin' ]);
        }
        return false;
    }

    protected function getFormUsages(){
        $usages = parent::getFormUsages();
        $usages[] = 'edit-admin';
        return $usages;
    }

    protected function addButtons(){
        parent::addButtons();
        if(in_array($this->usage, ['edit-admin']))
            $this->builder->add('_save',Type\SubmitType::class, array(
                'label'=>$this->translator->trans('save',[],'forms'),
                'attr'=>array(
                    'class'=>'btn btn-default submit'
                )
            ));

    }

}