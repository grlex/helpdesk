<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 08.09.2017
 * Time: 21:42
 */

namespace AppBundle\Form;


use AppBundle\Entity\Department;
use AppBundle\Entity\Role;
use AppBundle\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;



class UserType extends BaseEntityType {

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $this->add('name',TextType::class, array('label'=>'user.name'));
        $this->add('login',TextType::class, array('label'=>'user.login'));

        $this->addPasswordField();
        $this->add('password_repeat',PasswordType::class, array(
            'label'=>'user.password-repeat',
            'mapped'=>false,
            'always_empty'=>false
        ));

        $this->add('rolesMask',ChoiceType::class, array(
            'label'=>'user.roles',
            'choices'=> array_combine(Role::getRoles(),Role::getMaskBits()),
            'multiple'=>true,
            'attr'=>array('data-placeholder'=>$this->translator->trans('user.choose-roles',[],'forms')),

        ));
        if($builder->has('rolesMask')) $builder->get('rolesMask')->addModelTransformer(new CallbackTransformer(
            function($rolesMask){
                $normData = [];
                foreach(Role::getMaskBits() as $id=>$bit){
                    if($rolesMask&$bit) $normData[] = $bit;
                }
                return $normData;
            },
            function($normData){
                return array_reduce($normData,function($mask, $bit){
                    return $mask|$bit;
                }, 0);
            }
        ));
        $this->add('department',EntityType::class, array(
            'label'=>'user.department',
            'class'=>Department::class,
            'choice_label'=>'name'));

        $this->add('position',TextType::class, array('label'=>'user.position'));

        $this->addButtons();
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefault('data_class', User::class);
    }

    private function addPasswordField(){
        switch($this->usage) {
            case 'new':
                $this->add('password', PasswordType::class, array(
                    'label' => 'user.password',
                    'always_empty' => false
                ));
                break;
            case 'edit':
                $this->add('password', TextType::class, array(
                    'label' => 'user.password',
                ));
                break;
        }
    }

    protected function isFieldUsed($name){
        switch($name){
            case 'name': return in_array($this->usage, ['new', 'edit', ]);
            case 'login': return in_array($this->usage, ['new', 'edit', ]);
            case 'password': return in_array($this->usage, ['new', 'edit', ]);
            case 'password_repeat': return in_array($this->usage, ['new', ]);
            case 'rolesMask': return in_array($this->usage, ['new', 'edit', ]);
            case 'department': return in_array($this->usage, ['new', 'edit']);
            case 'position': return in_array($this->usage, ['new', 'edit', ]);
        }
        return false;
    }

}