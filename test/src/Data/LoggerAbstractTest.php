<?php

namespace Data;


use G4\CleanCore\Application;
use G4\Log\Data\Exclude;
use G4\Log\Data\Response;
use PHPUnit_Framework_TestCase;

class LoggerAbstractTest extends PHPUnit_Framework_TestCase
{
    private $app;
    private $profiler;
    private $request;
    private $logger;
    const TRUNCATED = 'TRUNCATED';


    protected function setUp()
    {
        $this->profiler = $this->createMock(\G4\Runner\Profiler::class);
        $this->profiler->method('getProfilerOutput')->willReturn([]);

        $this->request = new \G4\CleanCore\Request\Request();
        $response = new \G4\CleanCore\Response\Response();
        $response->setResponseObject('Testing long response that we want to truncate');

        $this->app = $this->createMock(Application::class);
        $this->app->method('getResponse')->willReturn($response);
        $this->app->method('getRequest')->willReturn($this->request);

        $this->logger = new Response();
        $this->logger->setApplication($this->app);
        $this->logger->setProfiler($this->profiler);
    }

    public function testFilterExcludedFields()
    {
        $this->app->method('getAppNamespace')->willReturn('test_module');
        $this->request->setResourceName('test_service');

        $exclude = new Exclude([
            'test_module' =>
                [
                    'test_service' => ['message', 'resource']
                ]
        ]);

        $this->logger->setExcluded($exclude);

        $rawData = $this->logger->getRawData();

        $this->assertTrue($rawData['message'] === self::TRUNCATED);
        $this->assertTrue($rawData['resource'] === self::TRUNCATED);
    }

    public function testFilterExcludedFieldsEmpty()
    {
        $this->app->method('getAppNamespace')->willReturn('test_module');
        $this->request->setResourceName('test_service');

        $exclude = new Exclude();

        $this->logger->setExcluded($exclude);

        $rawData = $this->logger->getRawData();

        $this->assertFalse($rawData['message'] === self::TRUNCATED);
        $this->assertFalse($rawData['resource'] === self::TRUNCATED);
    }

    public function testFilterExcludedFieldsModuleMismatch()
    {
        $this->app->method('getAppNamespace')->willReturn('testModule');
        $this->request->setResourceName('test_service');

        $exclude = new Exclude([
            'test_module' =>
                [
                    'test_service' => ['timestamp', 'hostname']
                ]
        ]);

        $this->logger->setExcluded($exclude);

        $rawData = $this->logger->getRawData();

        $this->assertFalse($rawData['message'] === self::TRUNCATED);
        $this->assertFalse($rawData['resource'] === self::TRUNCATED);
    }

    public function testFilterExcludedFieldsServiceMismatch()
    {
        $this->app->method('getAppNamespace')->willReturn('test_module');
        $this->request->setResourceName('testService');

        $exclude = new Exclude([
            'test_module' =>
                [
                    'test_service' => ['timestamp', 'hostname']
                ]
        ]);

        $this->logger->setExcluded($exclude);

        $rawData = $this->logger->getRawData();

        $this->assertFalse($rawData['message'] === self::TRUNCATED);
        $this->assertFalse($rawData['resource'] === self::TRUNCATED);
    }

    public function testFilterExcludedFieldsWithMethod()
    {
        $this->app->method('getAppNamespace')->willReturn('test_module');
        $this->request->setResourceName('test_service');
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $exclude = new Exclude([
            'test_module' =>
                [
                    'test_service' =>
                        [
                            'get' => ['message', 'resource']
                        ]
                ]
        ]);
        $exclude->setModule('test_module');
        $exclude->setService('test_service');

        $this->logger->setExcluded($exclude);

        $rawData = $this->logger->getRawData();

        $this->assertTrue($rawData['message'] === self::TRUNCATED);
        $this->assertTrue($rawData['resource'] === self::TRUNCATED);
    }

    public function testFilterExcludedFieldsMethodMismatch()
    {
        $this->app->method('getAppNamespace')->willReturn('test_module');
        $this->request->setResourceName('test_service');
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $exclude = new Exclude([
            'test_module' =>
                [
                    'test_service' =>
                        [
                            'put' => ['timestamp', 'hostname']
                        ]
                ]
        ]);

        $this->logger->setExcluded($exclude);

        $rawData = $this->logger->getRawData();

        $this->assertFalse($rawData['message'] === self::TRUNCATED);
        $this->assertFalse($rawData['resource'] === self::TRUNCATED);
    }
}