<?php

namespace Data;

use G4\Log\Data\Exclude;
use PHPUnit_Framework_TestCase;

class ExcludeTest extends PHPUnit_Framework_TestCase
{
    public function testGetExclude()
    {
        $exclude = new Exclude([
            'test_module' =>
                [
                    'test_service' => ['field_name']
                ]
        ]);
        $exclude->setModule('test_module');
        $exclude->setService('test_service');

        $this->assertSame(['field_name'], $exclude->getExclude());
    }

    public function testGetExcludeModuleMismatch()
    {
        $exclude = new Exclude([
            'test_module' =>
                [
                    'test_service' => ['field_name']
                ]
        ]);
        $exclude->setModule('testModule');
        $exclude->setService('test_service');

        $this->assertSame([], $exclude->getExclude());
    }

    public function testGetExcludeServiceMismatch()
    {
        $exclude = new Exclude([
            'test_module' =>
                [
                    'test_service' => ['field_name']
                ]
        ]);
        $exclude->setModule('test_module');
        $exclude->setService('testService');

        $this->assertSame([], $exclude->getExclude());
    }

    public function testGetExcludeWithMethod()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $exclude = new Exclude([
            'test_module' =>
                [
                    'test_service' =>
                        [
                            'post' => ['field_name']
                        ]
                ]
        ]);
        $exclude->setModule('Test_module');
        $exclude->setService('test_Service');

        $this->assertSame(['field_name'], $exclude->getExclude());
    }

    public function testGetExcludeMethodMismatch()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $exclude = new Exclude([
            'test_module' =>
                [
                    'test_service' =>
                        [
                            'test_method' => ['field_name']
                        ]
                ]
        ]);
        $exclude->setModule('test_module');
        $exclude->setService('test_service');

        $this->assertSame([], $exclude->getExclude());
    }
}