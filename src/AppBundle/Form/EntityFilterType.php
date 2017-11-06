<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 14.10.2017
 * Time: 9:38
 */

namespace AppBundle\Form;

use AppBundle\Form\Transformer\FilterModelTransformer;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use AppBundle\Entity\BaseEntity;

class EntityFilterType extends AbstractType{


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $fields = [$options['target_entity'], 'getFields'];
        $fields = call_user_func($fields);
        foreach($fields as $field){
                $builder->add($field, Type\TextType::class, [ 'required'=>false ] );
        }
        $builder->add('filter', Type\SubmitType::class);
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('csrf_protection', false);
        $resolver->setDefined('target_entity');
        $resolver->setRequired('target_entity');
        $resolver->setAllowedTypes('target_entity', 'string');

    }

}