<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 06.09.2017
 * Time: 22:09
 */

namespace AppBundle\Menu;



use Knp\Menu\FactoryInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;

class Builder {
    private $authChecker;
    private $factory;
    public function __construct(AuthorizationChecker $authChecker, FactoryInterface $factory){
        $this->authChecker = $authChecker;
        $this->factory = $factory;
    }

    public function createMainMenu(){

        $root = $this->factory->createItem('root',array())->setChildrenAttribute('class','nav navbar-nav navbar-left');

        $this->buildCatalogSection($root);
        $this->buildRequestSection($root);
        $this->buildUserSection($root);
        $root->addChild('main.logout', array('uri'=>'/account/logout'));

        $this->setTranslationDomainRecursive($root,'menus');
        $this->setDropDowns($root, 1);

        return $root;
    }

    private function buildCatalogSection($root, $options = []){

        if($this->authChecker->isGranted('ROLE_ADMIN')){
            $root->addChild('main.catalog');
            $root['main.catalog']->addChild('main.category.list', array('uri'=>'/category/list'));
            $root['main.catalog']->addChild('main.department.list', array('uri'=>'/department/list'));
            $root['main.catalog']->addChild('main.active.list', array('uri'=>'/active/list'));
        }
    }

    private function buildRequestSection($root, $options = []){
        $root->addChild('main.request');
        $root['main.request']->addChild('main.request.new', array('uri'=>'/request/new'));
        $root['main.request']->addChild('main.request.my-list', array('uri'=>'/request/my-list'));
        if($this->authChecker->isGranted('ROLE_MODERATOR'))
            $root['main.request']->addChild('main.request.distribute-list', array('uri'=>'/request/distribute-list'));
        if($this->authChecker->isGranted('ROLE_EXECUTOR'))
            $root['main.request']->addChild('main.request.process-list', array('uri'=>'/request/process-list'));
        if($this->authChecker->isGranted('ROLE_MODERATOR'))
            $root['main.request']->addChild('main.request.close-list', array('uri'=>'/request/close-list'));
        $root['main.request']->addChild('main.request.list', array('uri'=>'/request/list'));
    }

    private function buildUserSection($root, $options = []){
        if($this->authChecker->isGranted('ROLE_ADMIN')){
            $root->addChild('main.user');
            $root['main.user']->addChild('main.user.new', array('uri'=>'/user/new'));
            $root['main.user']->addChild('main.user.list', array('uri'=>'/user/list'));
        }
    }

    private function setTranslationDomainRecursive($menuItem, $domain){
        $menuItem->setExtra('translation_domain',$domain);
        $childItems = $menuItem->getChildren();
        foreach($childItems as $childItem) $this->setTranslationDomainRecursive($childItem, $domain);
    }
    private function setDropDowns($menuItem, $level){
        if($level==0 && $menuItem->hasChildren()) {
            $menuItem->setAttribute('dropdown', true);
            return;
        }
        $children = $menuItem->getChildren();
        $level--;
        foreach($children as $item) $this->setDropDowns($item, $level);

    }
} 