<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 19.09.2017
 * Time: 20:29
 */

namespace AppBundle\Controller;


use AppBundle\Service\FileStorage;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileController extends Controller {

    private $fileStorage;
    public function __construct(FileStorage $fileStorage){
        $this->fileStorage = $fileStorage;
    }


    /**
     * @param Request $request
     * @Route("/file/upload")
     * Method({"POST"})
     * @return JsonResponse
     */
    public function uploadAction(Request $request)
    {
        $file = $request->files->count() ? $request->files->get('file') : null;
        if (is_null($file)) {
            return new JsonResponse(array(
                'success' => false
            ));
        }

        $page = parse_url($request->headers->get('referer'),PHP_URL_PATH);
        $entity = $this->fileStorage->save($file, $page);


        return new JsonResponse(array(
            'success' => true,
            'data'=> array(
                'link'=> sprintf('/file/%s',$entity->getName()),
                'id'=> $entity->getId()
            )

        ));

    }

    /**
     * @param Request $request
     * @Route("/file/{name}")
     * @Method({"GET"})
     */
    public function downloadAction(Request $request, $name){
        $file = $this->fileStorage->getFile($name);
        if(!$file){
            throw $this->createNotFoundException('Requested file couldn\'t be served');
        }
        return new BinaryFileResponse($file);
    }
} 