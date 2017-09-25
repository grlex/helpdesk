<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 21.09.2017
 * Time: 20:09
 */

namespace AppBundle\Form;


use AppBundle\Entity\File;
use AppBundle\Form\Transformer\FileTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type;

class FileType extends AbstractType{

    private $transformer;
    public function __construct(FileTransformer $transformer){
        $this->transformer = $transformer;
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('id', Type\HiddenType::class, array(
            'required'=>false
        ));
        $builder->add('name', Type\HiddenType::class, array(
            'required'=>false
        ));

        $builder->addModelTransformer($this->transformer);
    }

}