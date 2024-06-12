<?php

namespace test\src;

use G4\CleanCore\Application;
use G4\Log\Adapter\Redis;
use G4\Log\Data\Exclude;
use G4\Log\Data\Response;

class LoggerTest extends \PHPUnit\Framework\TestCase
{
    const EXCLUDED = 'EXCLUDED';

    public function testLog()
    {
        $this->markTestSkipped('This test is skipped because could not mock sys_get_load_avg() function');
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $profiler = $this->createMock(\G4\Runner\Profiler::class);
        $profiler->method('getProfilerOutput')->willReturn(['test_profiler' => 'example']);
        $profiler->method('getProfilerSummary')->willReturn([]);

        $request = new \G4\CleanCore\Request\Request();
        $request->setResourceName('test_service');

        $app = $this->createMock(Application::class);
        $app->method('getResponse')->willReturn(new \G4\CleanCore\Response\Response());
        $app->method('getRequest')->willReturn($request);
        $app->method('getAppNamespace')->willReturn('test_module');

        $adapter = $this->createMock(Redis::class);
        $adapter
            ->expects($this->once())
            ->method('save')
            ->with(
                [
                    'id' => null,
                    'code' => 204,
                    'message' => 'No Content',
                    'resource' => null,
                    'app_code' => null,
                    'app_message' => null,
                    'elapsed_time' => self::EXCLUDED,
                    'elapsed_time_ms' => self::EXCLUDED,
                    'profiler' => '{"test_profiler":"example"}',
                    'app_version' => null,
                    'cpu_load_1' => 0.1,
                    'cpu_load_5' => 0.2,
                    'cpu_load_15' => 0.3,
                    'cpu_process' => 0.4,
                ]
            );

        $response = new Response();
        $response->setApplication($app);
        $response->setProfiler($profiler);

        $exclude = new Exclude([
            'test_module' =>
                [
                    'test_service' =>
                        [
                            'get' => ['elapsed_time', 'app_message', 'elapsed_time_ms']
                        ]
                ]
        ]);
        $exclude->setModule('test_module');
        $exclude->setService('test_service');

        $logger = new \G4\Log\Logger($adapter, $exclude);
        $logger->log($response);
    }
}