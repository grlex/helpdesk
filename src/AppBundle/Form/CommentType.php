<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 15.09.2017
 * Time: 22:35
 */

namespace AppBundle\Form;


use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type;
use AppBundle\Entity\User;
use AppBundle\Entity\Request;
use AppBundle\Entity\Comment;

class CommentType extends AbstractType{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('author', EntityType::class, array(
            'class'=>User::class,
            'choice_label'=>false
        ));
        $builder->add('content', Type\TextareaType::class);
        $builder->add('datetime', Type\DateTimeType::class);
        $builder->add('replies',Type\CollectionType::class, array(
            'entry_type'=>CommentType::class
        ));

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'translation_domain'=> 'forms'
        ));
    }

}