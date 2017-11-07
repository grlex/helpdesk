<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 07.11.2017
 * Time: 14:13
 */
namespace Tests\AppBundle\EntityListFilter\FilterBuilder;


use AppBundle\Entity\Request;
use AppBundle\Entity\Role;
use AppBundle\Entity\User;
use AppBundle\EntityListFilter\FilterBuilder\AppFilterBuilder;
use Doctrine\ORM\Mapping\ClassMetadata;
use PHPUnit\Framework\TestCase;

use Symfony\Component\Translation\Translator;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\Query\Expr\Func;
use Doctrine\ORM\Query\Expr\Comparison;

class AppFilterBuilderTest extends TestCase {
    private $metadata;
    private $filterBuilder;
    private function prepareEntityTest($entityClass){
        $this->metadata = $this->createMock(ClassMetadata::class);
        $this->metadata->method('getName')->willReturn($entityClass);
        $mockTranslator = $this->createMock(Translator::class);
        $mockTranslator->method('trans')->willReturnArgument(0);
        $mockDoctrine = $this->createMock(Registry::class);
        $this->filterBuilder = new AppFilterBuilder($mockTranslator, $mockDoctrine);
    }

    public function testGetFieldFilter(){
        // Request entity
        $this->prepareEntityTest(Request::class);
        $this->_testGetFieldFilterForRequestTextStatus();
        $this->_testGetFieldFilterForRequestNumStatus();
        $this->_testGetFieldFilterForRequestMultiStatus();

        // User entity
        $this->prepareEntityTest(User::class);
        $this->_testGetFieldFilterForUserRoles();
    }
    private function _testGetFieldFilterForRequestTextStatus(){
        //arrange
        $requestNumStatus = Request::STATUS_OPENED;
        $filterText = Request::getStatuses()[$requestNumStatus];
        //act
        $filter = $this->filterBuilder->getFieldFilter(
            'status',
            ['textStatus'=>$filterText],
            $this->metadata
        );
        //assert
        $this->assertInstanceOf(Func::class, $filter['expr']);
        $this->assertContains($requestNumStatus, $filter['expr']->getArguments());
    }
    private function _testGetFieldFilterForRequestNumStatus(){
        //arrange
        $requestNumStatus = Request::STATUS_CLOSED;
        $filterText = (string)$requestNumStatus;
        //act
        $filter = $this->filterBuilder->getFieldFilter(
            'status',
            ['status'=>$filterText],
            $this->metadata
        );
        //assert
        $this->assertInstanceOf(Func::class,$filter['expr']);
        $this->assertContains($requestNumStatus, $filter['expr']->getArguments());
    }
    private function _testGetFieldFilterForRequestMultiStatus(){
        //arrange
        $requestNumStatuses = array( Request::STATUS_OPENED, Request::STATUS_DISCARDED);
        $filterStatuses = $requestNumStatuses;
        //act
        $filter = $this->filterBuilder->getFieldFilter(
            'status',
            ['status'=>$filterStatuses],
            $this->metadata
        );
        //assert
        $this->assertInstanceOf(Func::class,$filter['expr']);
        $this->assertArraySubset($requestNumStatuses,$filter['expr']->getArguments());
    }
    private function _testGetFieldFilterForUserRoles(){
        //arrange
        $filterText = Role::getRoles()[Role::ROLE_ADMIN];
        $filterText = substr($filterText, -2);
        $adminMask = Role::getMaskBits()[Role::ROLE_ADMIN];
        //act
        $filter = $this->filterBuilder->getFieldFilter(
            'rolesMask',
            ['roles'=>$filterText],
            $this->metadata
        );

        //assert
        $this->assertInstanceOf(Comparison::class, $filter['expr']);
        $funcExpr = $filter['expr']->getLeftExpr();
        $this->assertInstanceOf(Func::class, $funcExpr);
        $this->assertEquals('BIT_AND', $funcExpr->getName());
        $this->assertRegExp('/^\w+\.rolesMask$/', $funcExpr->getArguments()[0]);
        $this->assertNotEquals($adminMask & (integer)($funcExpr->getArguments()[1]), 0);
    }
}


 