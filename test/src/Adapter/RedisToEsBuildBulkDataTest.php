<?php

namespace Adapter;

use G4\Log\Adapter\RedisToEsBuildBulkData;
use PHPUnit_Framework_TestCase;

class RedisToEsBuildBulkDataTest extends PHPUnit_Framework_TestCase
{
    public function testEmptyData()
    {
        $result = RedisToEsBuildBulkData::buildBulkData([], '');

        $this->assertEquals("\n", $result);
    }

    public function testMissingIndex()
    {
        $data = [[
            '_type' => 'core_requests',
            'id' => 'e22161e2ecb9d885123cef67884e8502',
        ]];
        ob_start();
        $result = RedisToEsBuildBulkData::buildBulkData($data, '');
        $output = ob_get_contents();
        ob_end_clean();

        $this->assertEquals("\n", $result);
        $this->assertContains("Undefined _index or _type", $output);
    }

    public function testMissingType()
    {
        $data = [[
            '_index' => 'nd_requests-2023-01-w05',
            'id' => 'e22161e2ecb9d885123cef67884e8502',
        ]];
        ob_start();
        $result = RedisToEsBuildBulkData::buildBulkData($data, '');
        $output = ob_get_contents();
        ob_end_clean();

        $this->assertEquals("\n", $result);
        $this->assertContains("Undefined _index or _type", $output);
    }

    /** @dataProvider dataFromRedis */
    public function testEmptyEsVersion($data)
    {
        $expectedResult = '{"index":{"_index":"nd_requests-2023-01-w05","_type":"core_requests","_id":"e22161e2ecb9d885123cef67884e8502"}}'. PHP_EOL;
        $expectedResult .= '{"id":"e22161e2ecb9d885123cef67884e8502","timestamp":1675083716643,"uuid":"54920437-699e-42f8-a843-6ac49ab82e8f","message":"Created","resource":{"user_data":{"user_id":"1000"},"user_status":11,"user_status_msg":"PENDING"}}'. PHP_EOL;

        $result = RedisToEsBuildBulkData::buildBulkData([$data], 'defaultES');

        $this->assertEquals($expectedResult, $result);
    }

    /** @dataProvider dataFromRedis */
    public function testBeforeEs6($data)
    {
        $expectedResult = '{"index":{"_index":"nd_requests-2023-01-w05","_type":"core_requests","_id":"e22161e2ecb9d885123cef67884e8502"}}'. PHP_EOL;
        $expectedResult .= '{"id":"e22161e2ecb9d885123cef67884e8502","timestamp":1675083716643,"uuid":"54920437-699e-42f8-a843-6ac49ab82e8f","message":"Created","resource":{"user_data":{"user_id":"1000"},"user_status":11,"user_status_msg":"PENDING"}}'. PHP_EOL;

        $result = RedisToEsBuildBulkData::buildBulkData([$data], 'es2');

        $this->assertEquals($expectedResult, $result);
    }

    /** @dataProvider dataFromRedis */
    public function testEs6($data)
    {
        $expectedResult = '{"index":{"_index":"nd_requests-2023-01-w05","_type":"_doc","_id":"e22161e2ecb9d885123cef67884e8502"}}'. PHP_EOL;
        $expectedResult .= '{"index_type":"core_requests","id":"e22161e2ecb9d885123cef67884e8502","timestamp":1675083716643,"uuid":"54920437-699e-42f8-a843-6ac49ab82e8f","message":"Created","resource":{"user_data":{"user_id":"1000"},"user_status":11,"user_status_msg":"PENDING"}}'. PHP_EOL;

        $result = RedisToEsBuildBulkData::buildBulkData([$data], 'es6');

        $this->assertEquals($expectedResult, $result);
    }

    /** @dataProvider dataFromRedis */
    public function testEs7($data)
    {
        $expectedResult = '{"index":{"_index":"nd_requests-2023-01-w05","_type":"_doc","_id":"e22161e2ecb9d885123cef67884e8502"}}'. PHP_EOL;
        $expectedResult .= '{"index_type":"core_requests","id":"e22161e2ecb9d885123cef67884e8502","timestamp":1675083716643,"uuid":"54920437-699e-42f8-a843-6ac49ab82e8f","message":"Created","resource":{"user_data":{"user_id":"1000"},"user_status":11,"user_status_msg":"PENDING"}}'. PHP_EOL;

        $result = RedisToEsBuildBulkData::buildBulkData([$data], 'es7');

        $this->assertEquals($expectedResult, $result);
    }

    /** @dataProvider dataFromRedis */
    public function testEs8($data)
    {
        $expectedResult = '{"index":{"_index":"nd_requests-2023-01-w05","_id":"e22161e2ecb9d885123cef67884e8502"}}'. PHP_EOL;
        $expectedResult .= '{"index_type":"core_requests","id":"e22161e2ecb9d885123cef67884e8502","timestamp":1675083716643,"uuid":"54920437-699e-42f8-a843-6ac49ab82e8f","message":"Created","resource":{"user_data":{"user_id":"1000"},"user_status":11,"user_status_msg":"PENDING"}}'. PHP_EOL;

        $result = RedisToEsBuildBulkData::buildBulkData([$data], 'es8');

        $this->assertEquals($expectedResult, $result);
    }

    public function dataFromRedis()
    {
        return [[[
                    '_index' => 'nd_requests-2023-01-w05',
                    '_type' => 'core_requests',
                    'id' => 'e22161e2ecb9d885123cef67884e8502',
                    'timestamp' => 1675083716643,
                    'uuid' => '54920437-699e-42f8-a843-6ac49ab82e8f',
                    'message' => 'Created',
                    'resource' => [
                        'user_data' => [
                            'user_id' => '1000',
                        ],
                        'user_status' => 11,
                        'user_status_msg' => 'PENDING'
                    ]
                ]]];
    }
}