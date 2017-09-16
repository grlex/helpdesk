<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 15.09.2017
 * Time: 23:07
 */

namespace AppBundle\Form;


use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class CommentCollectionType extends CollectionType {

    public function getBlockPrefix()
    {
        return 'comment_collection';
    }
}