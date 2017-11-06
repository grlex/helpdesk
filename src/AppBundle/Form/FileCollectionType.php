<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 12.10.2017
 * Time: 20:44
 */

namespace AppBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FileCollectionType extends AbstractType{


    public function getParent(){
        return CollectionType::class;
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);
        $view->vars['add_label'] = $options['add_label'];

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefined('add_label');
        $resolver->setDefault('add_label', 'add file');
    }
}