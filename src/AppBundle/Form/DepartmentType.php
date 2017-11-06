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


class DepartmentType extends BaseEntityType {


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $this->add('name',TextType::class, array('label'=>'department.name'));

        $this->addButtons();
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefault('data_class', Department::class);
    }

    protected function isFieldUsed($name){
        switch($name){
            case 'name': return in_array($this->usage, ['new', 'edit', ]);
        }
        return false;
    }
}