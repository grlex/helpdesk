<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 20.10.2017
 * Time: 12:06
 */

namespace AppBundle\Form;


use AppBundle\Entity\Comment;
use AppBundle\Entity\LifecycleStep;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use AppBundle\Entity\Request;
use AppBundle\Entity\User;

class LifecycleStepType extends BaseEntityType {

    protected $doctrine;
    public function __construct(Registry $doctrine, TranslatorInterface $translator){
        $this->doctrine = $doctrine;
        parent::__construct($translator);
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $this->add('executor', EntityType::class, array(
            'label'=>'request.executor',
            'class'=>User::class,
            'choice_label'=>'name',
            'multiple'=>false,
            'query_builder'=> $this->doctrine->getRepository('AppBundle:User')
                ->createQueryBuilder('u')
                ->join('u.roles','roles')
                ->where("roles.role='ROLE_EXECUTOR'")
        ));
        $this->add('comment', Type\TextareaType::class, array(
            'label'=>'lifecycleStep.comment',
            'required'=>false,
            //'vertical_layout'=>true
        ));

        $this->addButtons();


    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefined('vertical_layout');
        $resolver->setDefault('vertical_layout', false);
        $resolver->setDefault('form_usage', 'open');
    }
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['vertical_layout'] = $options['vertical_layout'];
    }


    protected function isFieldUsed($name){
        switch($name){
            case 'executor': return in_array($this->usage, ['distribute']);
            case 'comment': return in_array($this->usage, $this->getFormUsages());
        }
        return false;
    }

    protected function addButtons(){

        parent::addButtons();//cancel button
        $submitText = $this->translator->trans(sprintf('%s.request.submit',$this->usage),[],'forms');
        $this->builder->add('_do',Type\SubmitType::class, array(
            'label'=> $submitText,
            'attr'=>array(
                'class'=>'btn btn-default  submit'
            )
        ));

    }

    protected function getFormUsages(){
        $usages = [];//parent::getFormUsages();
        $usages[] = 'reject';
        $usages[] = 'reopen';
        $usages[] = 'distribute';
        $usages[] = 'process';
        $usages[] = 'accept';
        $usages[] = 'discard';
        $usages[] = 'close';
        return $usages;
    }



} 