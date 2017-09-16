<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 14.09.2017
 * Time: 16:13
 */

namespace AppBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Entity\Department;
use AppBundle\Form\DepartmentNewEditType;
class DepartmentController extends Controller {
    use EntityControllerTrait;
    public function __construct(){
        $this->initialize(Department::class, DepartmentNewEditType::class, DepartmentNewEditType::class);
    }
} 