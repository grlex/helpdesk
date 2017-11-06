<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 08.09.2017
 * Time: 21:42
 */

namespace AppBundle\Form;


use AppBundle\Entity\Department;
use AppBundle\Entity\User;
use Proxies\__CG__\AppBundle\Entity\Role;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Validator\Constraints\Expression;

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

        $this->add('roles',EntityType::class, array(
            'label'=>'user.roles',
            'class'=>Role::class,
            'choice_label'=>'role',
            'choice_translation_domain'=>'messages',
            'multiple'=>true,
            'attr'=>array('data-placeholder'=>$this->translator->trans('user.choose-roles',[],'forms'))
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
            case 'roles': return in_array($this->usage, ['new', 'edit', ]);
            case 'department': return in_array($this->usage, ['new', 'edit']);
            case 'position': return in_array($this->usage, ['new', 'edit', ]);
        }
        return false;
    }

}