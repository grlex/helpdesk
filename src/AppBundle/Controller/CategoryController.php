<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 14.09.2017
 * Time: 15:52
 */

namespace AppBundle\Controller;


use AppBundle\Entity\Category;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Form\CategoryNewEditType;
class CategoryController extends Controller {
    use EntityControllerTrait;
    public function __construct(){
        $this->initialize(Category::class, CategoryNewEditType::class, CategoryNewEditType::class);
    }
} 