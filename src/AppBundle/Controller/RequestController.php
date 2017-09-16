<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 14.09.2017
 * Time: 17:14
 */

namespace AppBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Entity\Request;

class RequestController extends Controller {
    use EntityControllerTrait;
    public function __construct(){
        $this->initialize(Request::class);
    }
} 