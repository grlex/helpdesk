<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 19.09.2017
 * Time: 20:29
 */

namespace AppBundle\Controller;


use AppBundle\FileManager\FileManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Symfony\Component\HttpFoundation\BinaryFileResponse;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class FileController extends Controller {

    private $fileManager;
    public function __construct(FileManager $fileManager){
        $this->fileManager = $fileManager;
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
                'success' => false,
                'no-files' => true
            ));
        }

        $entity = $this->fileManager->saveFile($file);


        return new JsonResponse(array(
            'success' => true,
            'data'=>array(
                'baseName' => $entity->getFilename(),
                'id' => $entity->getId(),
                'baseUrl'=>'/file/download/'
            )

        ));

    }

    /**
     * @param Request $request
     * @Route("/file/download/{name}")
     * @Method({"GET"})
     */
    public function downloadAction(Request $request, $name){
        $name = $this->fileManager->splitPrefixedName($name);
        $fileEntity = $this->fileManager->getFileMeta($name['baseName']);
        if(!$fileEntity){
            //return new Response();
            throw $this->createNotFoundException('Requested file not found');
        }
        $file = $this->fileManager->getFile($name['baseName'], $name['prefix']);
        $response = new BinaryFileResponse($file);

        $response->headers->set('Content-Disposition', sprintf(' attachment; filename=%s', $fileEntity->getOriginalName()));
        return $response;
    }
} 