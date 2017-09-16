<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 12.09.2017
 * Time: 19:53
 */
namespace AppBundle\Form;


use AppBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;


class BaseNewEditType extends AbstractType {


    protected function addButtons($usage, FormBuilderInterface $builder , $cancelUri){

        $builder->add('cancel',ButtonType::class, array(
            'label'=>'form.common.cancel',
            'attr'=>array(
                'onclick'=>sprintf('window.location.href="%s"',$cancelUri),
                'class'=>'discard btn btn-default'
            )
        ));
        $builder->add('save',SubmitType::class, array(
            'label'=>'form.common.save',
            'attr'=>array(
                'class'=>'accept btn btn-default'
            )
        ));

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined('formUsage');
        $resolver->setAllowedValues('formUsage',['new', 'edit']);

        $resolver->setDefaults(array(
            'csrf_protection'=>true,
            'csrf_filed_name'=>'_token',
            'csrf_token_id'=>'commonTokenId',
            'translation_domain'=>'forms',
            'formUsage'=>'new'
        ));
    }

}