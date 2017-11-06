<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 14.09.2017
 * Time: 15:54
 */

namespace AppBundle\Form;


use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use AppBundle\Entity\Active;
use AppBundle\Entity\Department;
use Symfony\Component\Translation\Translator;


class ActiveType extends BaseEntityType {

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $this->add('cabNumber',TextType::class, array('label'=>'active.cabNumber'));
        $this->add('department', EntityType::class, array(
            'label'=>'active.department',
            'class'=>Department::class,
            'choice_label'=>'name',
            'choice_translation_domain'=>'messages',
            'multiple'=>false,
            'attr'=>array('data-placeholder'=>$this->translator->trans('form.active.department',[],'forms'))
        ));

        $this->addButtons('/active/list');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefault('data_class',Active::class);
    }

    protected function isFieldUsed($name){
        switch($name){
            case 'cabNumber': return in_array($this->usage, ['new', 'edit', ]);
            case 'department': return in_array($this->usage, ['new', 'edit', ]);
        }
        return false;
    }
}