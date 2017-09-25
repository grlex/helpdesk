<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 14.09.2017
 * Time: 17:14
 */

namespace AppBundle\Controller;


use AppBundle\Entity\NamedEntityInterface;
use AppBundle\Service\FileStorage;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Entity\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

class RequestController extends Controller {
    use EntityControllerTrait;
    private $fileStorage;
    public function __construct(FileStorage $fileStorage){
        $this->fileStorage = $fileStorage;
        $this->initialize(Request::class);
    }
    protected function onPreNewPersist(NamedEntityInterface $request){
        $request->setStatus(Request::STATUS_OPENED);
        $page = $this->get('request_stack')->getCurrentRequest()->getPathInfo();
        $files = $request->getFiles()->filter(function($file){ return !$file->getConfirmed();});
        $this->fileStorage->confirmFiles($files,$page,false);
        $this->fileStorage->deleteUnconfirmedFiles($page);
    }
    protected function onPreEditPersist(NamedEntityInterface $request){
        $page = $this->get('request_stack')->getCurrentRequest()->getPathInfo();
        $files = $request->getFiles()->filter(function($file){ return !$file->getConfirmed();});
        $this->fileStorage->confirmFiles($files,$page,false);
        $this->fileStorage->deleteUnconfirmedFiles($page);
    }

    protected function onPostRemove(NamedEntityInterface $request){

        $this->fileStorage->deleteFiles($request->getFiles(),false);

    }

    /**
     * @Route("/request/cancel")
     * @Method({"GET"})
     * @param HttpRequest $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     */
    public function cancelAction(HttpRequest $request){
        $page = $request->headers->get('referer');
        $page = parse_url($page, PHP_URL_PATH);
        $this->fileStorage->deleteUnconfirmedFiles($page);
        return $this->redirect('/request/list');
    }




    /**
     * @param $id
     * @Route("/request/view/{id}")
     * @Method({"GET"})
     */
    public function viewAction($id){
        return $this->render('request/view.html.twig', array());
    }
} 