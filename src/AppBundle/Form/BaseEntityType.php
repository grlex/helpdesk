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
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Form\Extension\Core\Type;


abstract class BaseEntityType extends AbstractType {

    protected $builder;
    protected $usage;
    protected $cancelUri;
    protected $translator;
    public function __construct(TranslatorInterface $translator){
        $this->translator = $translator;
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->builder = $builder;
        $this->usage = $options['form_usage'];
        $this->cancelUri = $options['cancel_uri'];
        $builder->add('_back_uri', Type\HiddenType::class, array(
            'mapped'=>false
        ));

    }
    protected function addButtons(){

        $cancelButtonAttributess = array('class'=>'btn btn-default cancel');
        if($this->cancelUri) $cancelButtonAttributess['onclick'] = sprintf('window.location.href="%s"',$this->cancelUri);
        $this->builder->add('_cancel',ButtonType::class, array(
            'label'=>$this->translator->trans('cancel',[],'forms'),
            'attr'=>$cancelButtonAttributess
        ));
        if(in_array($this->usage, ['new', 'edit']))
            $this->builder->add('_save',SubmitType::class, array(
                'label'=>$this->translator->trans('save',[],'forms'),
                'attr'=>array(
                    'class'=>'btn btn-default submit'
                )
            ));
        if(in_array($this->usage, ['remove']))
            $this->builder->add('_remove',SubmitType::class, array(
                'label'=>$this->translator->trans('remove',[],'forms'),
                'attr'=>array(
                    'class'=>'btn btn-default submit'
                )
            ));

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined('cancel_uri');
        $resolver->setDefault('cancel_uri', false);
        $resolver->setDefined('form_usage');
        $resolver->setAllowedValues('form_usage', $this->getFormUsages());

        $resolver->setDefaults(array(
            'csrf_protection'=>true,
            'csrf_filed_name'=>'_token',
            'csrf_token_id'=>'commonTokenId',
            'translation_domain'=>'entities',
            'form_usage'=>'new'
        ));
    }

    protected function getFormUsages(){
        return ['new', 'edit', 'remove', 'all', 'buttons' ];
    }

    protected function add($name, $typeClass=null, $options=array()){
        if($this->usage=='buttons') return;
        if($this->isFieldUsed($name) or $this->usage=='all') {
            $this->builder->add($name, $typeClass, $options);
        }
    }
    protected abstract function isFieldUsed($name);

}