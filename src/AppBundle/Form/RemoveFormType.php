<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 10.09.2017
 * Time: 9:26
 */

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class RemoveFormType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('remove',SubmitType::class, array('label'=>'form.common.remove'));
        $builder->add('cancel',SubmitType::class, array('label'=>'form.common.cancel'));
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