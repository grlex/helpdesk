<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 21.10.2017
 * Time: 12:50
 */

namespace AppBundle\Form;


use AppBundle\Entity\Comment;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommentType extends AbstractType{

    private $doctrine;
    public function __construct(Registry $doctrine){
        $this->doctrine = $doctrine;
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('id', Type\HiddenType::class);
        $builder->add('rawBody', Type\TextareaType::class, array(
            'label'=>false
        ));
        //$builder->add('commentBody', Type\Textarea::class);
        $builder->addModelTransformer(new CallbackTransformer(
            function(Comment $model = null){
                if(is_null($model)) return null;
                return [
                    'id'=> $model->getId(),
                    'rawBody' => $model->getRawBody()
                ];
            },
            function($normData){
                $comment = new Comment();
                if($normData['id']){
                    $comment = $this->doctrine->getRepository('AppBundle:Comment')->find($normData['id']);
                }
                $comment->setRawBody($normData['rawBody']);
                return $comment;
            }
        ));
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['vertical_layout'] = $options['vertical_layout'];
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined('vertical_layout');
        $resolver->setDefault('vertical_layout', false);
    }
}