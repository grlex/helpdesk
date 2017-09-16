<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 06.09.2017
 * Time: 22:09
 */

namespace AppBundle\Menu;


use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class Builder implements ContainerAwareInterface {
    use ContainerAwareTrait; // $this->container

    public function mainMenu(FactoryInterface $factory, array $options){

        $root = $factory->createItem('root',array())->setChildrenAttribute('class','nav navbar-nav navbar-left');

        $authChecker = $this->container->get('security.authorization_checker');

        if($authChecker->isGranted('ROLE_ADMIN')){
            $this->buildAdminMenu($root, $options);
        }
        else if($authChecker->isGranted('ROLE_MODERATOR')){
            $this->buildModeratormenu($root, $options);
        }
        else if($authChecker->isGranted('ROLE_EXECUTOR')){
            $this->buildExecutorMenu($root, $options);
        }
        else if($authChecker->isGranted('ROLE_USER')){
            $this->buildUserMenu($root, $options);
        }
        else{

        }
        $this->setTranslationDomainRecursive($root,'menus');
        $this->setDropDowns($root, 1);

        return $root;
    }
    private function buildAdminMenu($root, $options){
        $root->addChild('main.definition');
        $root['main.definition']->addChild('main.category.list', array('uri'=>'/category/list'));
        $root['main.definition']->addChild('main.department.list', array('uri'=>'/department/list'));
        $root['main.definition']->addChild('main.active.list', array('uri'=>'/active/list'));

        $root->addChild('main.request');
        $root['main.request']->addChild('main.request.my', array('uri'=>'/request/my'));
        $root['main.request']->addChild('main.request.new', array('uri'=>'/request/new'));
        $root['main.request']->addChild('main.request.list', array('uri'=>'/request/list'));

        $root->addChild('main.user');
        $root['main.user']->addChild('main.user.new', array('uri'=>'/user/new'));
        $root['main.user']->addChild('main.user.list', array('uri'=>'/user/list'));

        $root->addChild('main.logout', array('uri'=>'/account/logout'));
    }
    private function buildModeratorMenu($root, $options){
        $root->addChild('main.request');
        $root['main.request']->addChild('main.request.my', array('uri'=>'/request/my'));
        $root['main.request']->addChild('main.request.new', array('uri'=>'/request/new'));
        $root['main.request']->addChild('main.request.assign', array('uri'=>'/request/assign'));

        $root->addChild('main.user');
        $root['main.user']->addChild('main.user.list', array('uri'=>'/user/list'));

        $root->addChild('main.logout', array('uri'=>'/account/logout'));
    }
    private function buildExecutorMenu($root, $options){
        $root->addChild('main.request');
        $root['main.request']->addChild('main.request.my', array('uri'=>'/request/my'));
        $root['main.request']->addChild('main.request.new', array('uri'=>'/request/new'));
        $root['main.request']->addChild('main.request.process', array('uri'=>'/request/process'));

        $root->addChild('main.user');
        $root['main.user']->addChild('main.user.list', array('uri'=>'/user/list'));

        $root->addChild('main.logout', array('uri'=>'/account/logout'));
    }
    private function buildUserMenu($root, $options){
        $root->addChild('main.request', array('uri'=>''));
        $root['main.request']->addChild('main.request.my', array('uri'=>'/request/my'));
        $root['main.request']->addChild('main.request.new', array('uri'=>'/request/new'));

        $root->addChild('main.user', array('uri'=>''));
        $root['main.user']->addChild('main.user.list', array('uri'=>'/user/list'));

        $root->addChild('main.logout', array('uri'=>'/account/logout'));
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