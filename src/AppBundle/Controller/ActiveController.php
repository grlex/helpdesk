<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 14.09.2017
 * Time: 15:52
 */

namespace AppBundle\Controller;


use AppBundle\Entity\Active;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Form\ActiveNewEditType;
class ActiveController extends Controller {
    use EntityControllerTrait;
    public function __construct(){
        $this->initialize(Active::class, ActiveNewEditType::class, ActiveNewEditType::class);
    }
} 