<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 20.09.2017
 * Time: 20:53
 */

namespace AppBundle\FileManager;


use AppBundle\FileManager\Cleaner\CleanerInterface;
use AppBundle\FileManager\Storage\StorageInterface;
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

class FileManager  {
    const STATUS_CONFIRMED = 1;
    const STATUS_UNCONFIRMED = 2;
    const STATUS_ALL = 3;

    protected $storage;
    protected $cleaner;
    protected $doctrine;

    public function __construct(StorageInterface $storage,
                                CleanerInterface $cleaner,
                                Registry $doctrine){

        $this->doctrine = $doctrine;

        $this->storage = $storage;


        $this->cleaner = $cleaner;
        $this->cleaner->setFileManager($this);
    }

    public function getStorage(){
        return $this->storage;
    }

    public function getCleaner(){
        return $this->cleaner;
    }


    /**
     * @param $file File|UploadedFile
     * @param $persist bool Whether persist file entity with doctrine
     * @return FileEntity
     */

    public function saveFile( File $file, $confirmed=false){

        $baseName =  $this->storage->save($file);

        $originalName = $file->getClientOriginalName();



        $entity = new FileEntity();
        $entity->setOriginalName($originalName);
        $entity->setConfirmed($confirmed);
        $entity->setFilename($baseName);
        $entity->setCreated(new \DateTime());

        $em = $this->doctrine->getManager();
        $em->persist($entity);
        $em->flush();

        return $entity;

    }

    /**
     * @param string|FileEntity $file
     */
    public function deleteFile($file){

        $baseName = is_string($file) ? $file : $file->getFilename();

        $this->storage->delete($baseName);

        $queryBuilder = $this->doctrine->getEntityManager()
            ->createQueryBuilder()
            ->delete('AppBundle\Entity\File', 'f');

        if( is_string($file) )
            $queryBuilder->where(sprintf('f.filename = %s', $file));
        else
            $queryBuilder->where(sprintf('f.id = %d', $file->getId()));

        return $queryBuilder->getQuery()->execute();
    }

    /**
     * @param string[]|FileEntity[]|Collection $files
     */
    public function deleteFiles( $files){
        if(empty($files)) return;

        $files = $files instanceof Collection ? $files->toArray() : $files;


        $queryBuilder = $this->doctrine->getEntityManager()
            ->createQueryBuilder()
            ->delete('AppBundle\Entity\File', 'f');

        if(is_string($files[0])){
            foreach($files as &$file) {
                $this->storage->delete($file);
                $file = "'$file'";
            }

            $queryBuilder->where(sprintf('f.filename in (%s)', join(',', $files)));

        }
        else {
            foreach($files as &$file){
                $this->storage->delete($file->getFilename());
                $file = $file->getId();
            }

            $queryBuilder->where(sprintf('f.id in (%s)', join(',', $files)));
        }


        return $queryBuilder->getQuery()->execute();
    }


    public function getFile($baseName, $prefix=''){

        return $this->storage->get($baseName, $prefix);
    }
    public function splitPrefixedName($prefixedName){
        $nameSegments = array_reverse(mb_split('\.',$prefixedName));
        if(count($nameSegments)<2 or count($nameSegments)>3) return null;

        $baseName = $nameSegments[1].'.'.$nameSegments[0];
        $prefix = count($nameSegments)==3 ? $nameSegments[2] : null;
        return array(
            'baseName'=> $baseName,
            'prefix'=> $prefix
        );
    }

    public function getFileMetas($fileStatus = self::STATUS_ALL){

        $queryBuilder = $this->doctrine->getRepository('AppBundle:File')->createQueryBuilder('f');
        if($fileStatus == FileEntity::STATUS_CONFIRMED) $queryBuilder->where('f.confirmed=1');
        if($fileStatus == FileEntity::STATUS_UNCONFIRMED) $queryBuilder->where('f.confirmed=0');
        return $queryBuilder->getQuery()->execute();
    }
    public function getFileMeta($filename){

        return $this->doctrine->getEntityManager()
            ->createQuery('select f from AppBundle:File f where f.filename=:filename')
            ->setParameter(':filename',$filename)
            ->getOneOrNullResult();
    }


    /**
     * Doctrine event listener on PreRemove event for AppBundle\Entity\File entity
     * @param LifecycleEventArgs $eventArgs
     */
    public function preRemove(LifecycleEventArgs $eventArgs){

        $entity = $eventArgs->getObject();

        if( !$entity instanceof FileEntity) return;
        $this->storage->delete($entity->getFilename());

    }


}