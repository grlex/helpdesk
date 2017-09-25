<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 20.09.2017
 * Time: 20:53
 */

namespace AppBundle\Service;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\PersistentCollection;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Doctrine\Bundle\DoctrineBundle\Registry;
use AppBundle\Entity\File as FileEntity;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class FileStorage  {
    protected $storagePath;
    protected $doctrine;
    protected $session;
    protected static $batchLimit = 1000;

    public function __construct($storagePath, Registry $doctrine, SessionInterface $session){
        $this->storagePath = $storagePath;
        $this->doctrine = $doctrine;
        $this->session = $session;
    }


    /**
     * @param $file File|UploadedFile
     * @param $persist bool Whether persist file entity with doctrine
     * @return FileEntity
     */

    public function save( File $file, $page = '/', $confirmed=false){

        $extension = $file->getClientOriginalExtension();
        $origName = $file->getClientOriginalName();

        $entity = new FileEntity();
        $entity->setOriginalName($origName);
        $entity->setExtension($extension);
        $entity->setSize($file->getSize());
        $entity->setConfirmed(false);

        $em = $this->doctrine->getManager();
        $em->persist($entity);
        $em->flush();


        if(!$confirmed) {

            $unconfirmedFiles = $this->session->get('_unconfirmed_files', []);

            $unconfirmedFiles = array_merge_recursive($unconfirmedFiles, array(
                $page => array(
                    $entity->getName() => $entity->getId()
                )
            ));
            $this->session->set('_unconfirmed_files',$unconfirmedFiles);


        }


        $directoryPath = sprintf('%s/%d', $this->storagePath, $entity->getId() / self::$batchLimit);
        $file->move($directoryPath, $entity->getName());
        return $entity;

    }

    public function deleteUnconfirmedFiles($page=null, $updateDb=true){

        $session = $this->session;

        $pages = $session->get('_unconfirmed_files', []);

        $pagesToDelete = is_null($page) ? $pages
                                        : array_key_exists($page,$pages) ? [$page => $pages[$page]]
                                                                         : [ ];
        if(!count($pagesToDelete)) return;

        $fileIds = '';
        foreach($pagesToDelete as $page=>$ids){

            foreach($ids as $name=>$id){
                $filePath = $this->getFilePath($name);
                if(file_exists($filePath))
                    unlink($filePath);
            }

            $fileIds.= ','.join(',',$ids);

            unset($pages[$page]);

        }
        $session->set('_unconfirmed_files',$pages);

        $fileIds = trim($fileIds,',');
        if($updateDb and !empty($fileIds)){
            $this->doctrine->getManager()
                ->createQuery(sprintf('delete * from AppBundle\Enttiy\File f where f.id in [ %s ]', $fileIds ));
        }
    }

    /**
     * @param Collection|FileEntity[] $files
     * @param bool $updateDb
     */
    public function deleteFiles($files, $updateDb=true){
        $fileIds = '';
        foreach($files as $file){
            $fileIds .= ','.$file->getId();
            $filePath = sprintf('%s/%d/%s', $this->storagePath, $file->getId(), $file->getName());
            if(file_exists($filePath))
                unlink($filePath);
        }
        $fileIds = trim($fileIds, ',');
        if($updateDb and !empty($fileIds)) {

            $this->doctrine->getManager()
                ->createQuery(sprintf('delete * from AppBundle\Enttiy\File f where f.id in [ %s ]', $fileIds));
        }
    }

    public static function onSessionDestroy(AttributeBag $attributeBag, $storagePath, $doctrine, $updateDb=true){
        if(!$attributeBag->has('_unconfirmed_files')) return;
        $pages = $attributeBag->get('_unconfirmed_files');
        $fileIds = '';
        foreach($pages as $page=>$ids){
            foreach($ids as $name=>$id){
                $filePath = sprintf('%s/%d/%s', $storagePath, $id/self::$batchLimit, $name );
                if(file_exists($filePath))
                    unlink($filePath);
            }
            $fileIds.= ','.join(',',$ids);
        }
        $fileIds = trim($fileIds, ',');
        if($updateDb and !empty($fileIds)) {

            $doctrine->getManager()
                ->createQuery(sprintf('delete * from AppBundle\Enttiy\File f where f.id in [ %s ]', $fileIds));
        }

    }

    /**
     * Doctrine event listener on PreRemove event
     * @param LifecycleEventArgs $eventArgs
     */
    public function preRemove(LifecycleEventArgs $eventArgs){

        $entity = $eventArgs->getObject();

        if( !$entity instanceof FileEntity) return;
        $filePath = $this->getFilePath($entity->getName());
        if(file_exists($filePath)){
            unlink($filePath);
        }

    }

    public function confirmFile(FileEntity $file, $page=null, $persist=false){



        $file->setConfirmed(true);
        if ($persist) {
            $this->doctrine->getManager()
                ->createQuery(sprintf('update  AppBundle\Enttiy\File f set f.confirmed = 1 where f.id = %d ', $file->getId()));
        }


        $session = $this->session;
        $pages = $session->get('_unconfirmed_files', []);


        $pagesToCheck = is_null($page) ? $pages
                                        : array_key_exists($page,$pages) ? [$page => $pages[$page]]
                                                                         : [ ];
        if(!count($pagesToCheck)) return;

        foreach($pagesToCheck as $page => $ids){

            $fileId = $file->getId();
            if (($name = array_search($fileId, $ids)) !== false) {
                unset($pages[$page][$name]);
                $this->session->set('_unconfirmed_files', $pages);
                return;
            }
        }

    }

    /**
     * @param Collection|File[] $files
     * @param null $page
     * @param bool $persist
     */
    public function confirmFiles($files, $page=null, $persist=false){

        $files = is_array($files) ? new ArrayCollection($files): $files;
        foreach($files as $file){
            $this->confirmFile($file, $page, false);
        }

        if($persist) {
            $confirmIds = $files->map(function($fileEntity){ return $fileEntity->getId();});
            $this->doctrine->getManager()
                ->createQuery(sprintf('update  AppBundle\Enttiy\File f set f.confirmed = 1 where f.id in [ %s ]', join(',', $confirmIds)));
        }

    }




    /**
     * @param $name
     * @return bool|File
     */
    public function getFilePath($name){
        $filePath = sprintf('%s/%d/%s', $this->storagePath, $this->getIdFromName($name)/self::$batchLimit, $name );
        return $filePath;
    }



    /**
     * @param $name
     * @return bool
     */
    public function has($name){
        $id = $this->getIdFromName($name);
        return (bool) $this->doctrine->getManager()
            ->createQuery('select count(f) from AppBundle\Entity\File f where f.id=:id')
            ->setParameter(':id',$id)
            ->getSingleScalarResult();
    }

    public function getFile($name){
        return new File($this->getFilePath($name));
    }
    public function getFileEntity($id){
        return $this->doctrine->getRepository(FileEntity::class)->find($id);
    }

    public function getStoragePath(){
        return $this->storagePath;
    }

    private function getIdFromName($name){
        return intval($name);
    }

}