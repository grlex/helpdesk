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
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class CommonEntityController extends Controller {
    use EntityControllerTrait{
        getResponse as private _getResponse;
    }
    public function __construct(RequestStack $requestStack){
        $request= $requestStack->getCurrentRequest();
        $pathInfo = $request->getPathInfo();
        $entityName = mb_split('/',$pathInfo)[1];
        $this->initialize('AppBundle\\Entity\\'.ucfirst($entityName));
    }

    protected function canAct($action, array $entities)
    {
        return $this->isGranted('ROLE_ADMIN');
    }

    protected function getResponse($viewName, array  $viewVars = []){
        $response = $this->_getResponse($viewName, $viewVars);
        if($response->getStatusCode()==Response::HTTP_NOT_ACCEPTABLE){
            $viewBaseDir = $this->viewBaseDir;
            $this->viewBaseDir = 'commonEntity';
            $response = $this->_getResponse($viewName, $viewVars);
            $this->viewBaseDir = $viewBaseDir;
        }
        return $response;
    }
}