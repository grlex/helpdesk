<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 12.09.2017
 * Time: 19:51
 */

namespace AppBundle\Form;



use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use AppBundle\Entity\Department;


class DepartmentNewEditType extends BaseNewEditType {


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name',TextType::class, array('label'=>'form.department.name'));
        $this->addButtons($options['formUsage'], $builder, '/department/list');
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefault('data_class', Department::class);
    }
}