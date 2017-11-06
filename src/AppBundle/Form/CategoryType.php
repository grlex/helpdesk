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
use AppBundle\Entity\Category;


class CategoryType extends BaseEntityType {


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $this->add('name',TextType::class, array('label'=>'category.name'));

        $this->addButtons();
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefault('data_class',Category::class);
    }

    protected function isFieldUsed($name){
        switch($name){
            case 'name': return in_array($this->usage, ['new', 'edit', ]);
        }
        return false;
    }
}