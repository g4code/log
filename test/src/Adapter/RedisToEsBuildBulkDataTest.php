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
    public function testBulkDataSuccess($esVersion, $data, $expectedHeader, $expectedBody)
    {
        $expectedResult = $expectedHeader . $expectedBody;

        $result = RedisToEsBuildBulkData::buildBulkData([$data], $esVersion);

        $this->assertEquals($expectedResult, $result);
    }

    public function dataFromRedis()
    {
        $data = [
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
        ];

        return [
            //without __method value - default to index/create
            [
                'defaultES',
                $data,
                '{"index":{"_index":"nd_requests-2023-01-w05","_id":"e22161e2ecb9d885123cef67884e8502","_type":"core_requests"}}'. PHP_EOL,
                '{"id":"e22161e2ecb9d885123cef67884e8502","timestamp":1675083716643,"uuid":"54920437-699e-42f8-a843-6ac49ab82e8f","message":"Created","resource":{"user_data":{"user_id":"1000"},"user_status":11,"user_status_msg":"PENDING"}}'. PHP_EOL,
            ],
            [
                'es2',
                $data,
                '{"index":{"_index":"nd_requests-2023-01-w05","_id":"e22161e2ecb9d885123cef67884e8502","_type":"core_requests"}}'. PHP_EOL,
                '{"id":"e22161e2ecb9d885123cef67884e8502","timestamp":1675083716643,"uuid":"54920437-699e-42f8-a843-6ac49ab82e8f","message":"Created","resource":{"user_data":{"user_id":"1000"},"user_status":11,"user_status_msg":"PENDING"}}'. PHP_EOL,
            ],
            [
                'es6',
                $data,
                '{"index":{"_index":"nd_requests-2023-01-w05","_id":"e22161e2ecb9d885123cef67884e8502","_type":"_doc"}}'. PHP_EOL,
                '{"index_type":"core_requests","id":"e22161e2ecb9d885123cef67884e8502","timestamp":1675083716643,"uuid":"54920437-699e-42f8-a843-6ac49ab82e8f","message":"Created","resource":{"user_data":{"user_id":"1000"},"user_status":11,"user_status_msg":"PENDING"}}'. PHP_EOL,
            ],
            [
                'es7',
                $data,
                '{"index":{"_index":"nd_requests-2023-01-w05","_id":"e22161e2ecb9d885123cef67884e8502","_type":"_doc"}}'. PHP_EOL,
                '{"index_type":"core_requests","id":"e22161e2ecb9d885123cef67884e8502","timestamp":1675083716643,"uuid":"54920437-699e-42f8-a843-6ac49ab82e8f","message":"Created","resource":{"user_data":{"user_id":"1000"},"user_status":11,"user_status_msg":"PENDING"}}'. PHP_EOL,
            ],
            [
                'es8',
                $data,
                '{"index":{"_index":"nd_requests-2023-01-w05","_id":"e22161e2ecb9d885123cef67884e8502"}}'. PHP_EOL,
                '{"index_type":"core_requests","id":"e22161e2ecb9d885123cef67884e8502","timestamp":1675083716643,"uuid":"54920437-699e-42f8-a843-6ac49ab82e8f","message":"Created","resource":{"user_data":{"user_id":"1000"},"user_status":11,"user_status_msg":"PENDING"}}'. PHP_EOL,
            ],


            //__method = 'index'
            [
                'defaultES',
                array_merge($data, ['__method' => 'index']),
                '{"index":{"_index":"nd_requests-2023-01-w05","_id":"e22161e2ecb9d885123cef67884e8502","_type":"core_requests"}}'. PHP_EOL,
                '{"id":"e22161e2ecb9d885123cef67884e8502","timestamp":1675083716643,"uuid":"54920437-699e-42f8-a843-6ac49ab82e8f","message":"Created","resource":{"user_data":{"user_id":"1000"},"user_status":11,"user_status_msg":"PENDING"}}'. PHP_EOL,
            ],
            [
                'es2',
                array_merge($data, ['__method' => 'index']),
                '{"index":{"_index":"nd_requests-2023-01-w05","_id":"e22161e2ecb9d885123cef67884e8502","_type":"core_requests"}}'. PHP_EOL,
                '{"id":"e22161e2ecb9d885123cef67884e8502","timestamp":1675083716643,"uuid":"54920437-699e-42f8-a843-6ac49ab82e8f","message":"Created","resource":{"user_data":{"user_id":"1000"},"user_status":11,"user_status_msg":"PENDING"}}'. PHP_EOL,
            ],
            [
                'es6',
                array_merge($data, ['__method' => 'index']),
                '{"index":{"_index":"nd_requests-2023-01-w05","_id":"e22161e2ecb9d885123cef67884e8502","_type":"_doc"}}'. PHP_EOL,
                '{"index_type":"core_requests","id":"e22161e2ecb9d885123cef67884e8502","timestamp":1675083716643,"uuid":"54920437-699e-42f8-a843-6ac49ab82e8f","message":"Created","resource":{"user_data":{"user_id":"1000"},"user_status":11,"user_status_msg":"PENDING"}}'. PHP_EOL,
            ],
            [
                'es7',
                array_merge($data, ['__method' => 'index']),
                '{"index":{"_index":"nd_requests-2023-01-w05","_id":"e22161e2ecb9d885123cef67884e8502","_type":"_doc"}}'. PHP_EOL,
                '{"index_type":"core_requests","id":"e22161e2ecb9d885123cef67884e8502","timestamp":1675083716643,"uuid":"54920437-699e-42f8-a843-6ac49ab82e8f","message":"Created","resource":{"user_data":{"user_id":"1000"},"user_status":11,"user_status_msg":"PENDING"}}'. PHP_EOL,
            ],
            [
                'es8',
                array_merge($data, ['__method' => 'index']),
                '{"index":{"_index":"nd_requests-2023-01-w05","_id":"e22161e2ecb9d885123cef67884e8502"}}'. PHP_EOL,
                '{"index_type":"core_requests","id":"e22161e2ecb9d885123cef67884e8502","timestamp":1675083716643,"uuid":"54920437-699e-42f8-a843-6ac49ab82e8f","message":"Created","resource":{"user_data":{"user_id":"1000"},"user_status":11,"user_status_msg":"PENDING"}}'. PHP_EOL,
            ],

            //__method = update
            [
                'defaultES',
                array_merge($data, ['__method' => 'update']),
                '{"update":{"_index":"nd_requests-2023-01-w05","_id":"e22161e2ecb9d885123cef67884e8502","_type":"core_requests"}}'. PHP_EOL,
                '{"doc":{"id":"e22161e2ecb9d885123cef67884e8502","timestamp":1675083716643,"uuid":"54920437-699e-42f8-a843-6ac49ab82e8f","message":"Created","resource":{"user_data":{"user_id":"1000"},"user_status":11,"user_status_msg":"PENDING"}}}'. PHP_EOL,
            ],
            [
                'es2',
                array_merge($data, ['__method' => 'update']),
                '{"update":{"_index":"nd_requests-2023-01-w05","_id":"e22161e2ecb9d885123cef67884e8502","_type":"core_requests"}}'. PHP_EOL,
                '{"doc":{"id":"e22161e2ecb9d885123cef67884e8502","timestamp":1675083716643,"uuid":"54920437-699e-42f8-a843-6ac49ab82e8f","message":"Created","resource":{"user_data":{"user_id":"1000"},"user_status":11,"user_status_msg":"PENDING"}}}'. PHP_EOL,
            ],
            [
                'es6',
                array_merge($data, ['__method' => 'update']),
                '{"update":{"_index":"nd_requests-2023-01-w05","_id":"e22161e2ecb9d885123cef67884e8502","_type":"_doc"}}'. PHP_EOL,
                '{"doc":{"index_type":"core_requests","id":"e22161e2ecb9d885123cef67884e8502","timestamp":1675083716643,"uuid":"54920437-699e-42f8-a843-6ac49ab82e8f","message":"Created","resource":{"user_data":{"user_id":"1000"},"user_status":11,"user_status_msg":"PENDING"}}}'. PHP_EOL,
            ],
            [
                'es7',
                array_merge($data, ['__method' => 'update']),
                '{"update":{"_index":"nd_requests-2023-01-w05","_id":"e22161e2ecb9d885123cef67884e8502","_type":"_doc"}}'. PHP_EOL,
                '{"doc":{"index_type":"core_requests","id":"e22161e2ecb9d885123cef67884e8502","timestamp":1675083716643,"uuid":"54920437-699e-42f8-a843-6ac49ab82e8f","message":"Created","resource":{"user_data":{"user_id":"1000"},"user_status":11,"user_status_msg":"PENDING"}}}'. PHP_EOL,
            ],
            [
                'es8',
                array_merge($data, ['__method' => 'update']),
                '{"update":{"_index":"nd_requests-2023-01-w05","_id":"e22161e2ecb9d885123cef67884e8502"}}'. PHP_EOL,
                '{"doc":{"index_type":"core_requests","id":"e22161e2ecb9d885123cef67884e8502","timestamp":1675083716643,"uuid":"54920437-699e-42f8-a843-6ac49ab82e8f","message":"Created","resource":{"user_data":{"user_id":"1000"},"user_status":11,"user_status_msg":"PENDING"}}}'. PHP_EOL,
            ],


            //__method = delete
            [
                'defaultES',
                array_merge($data, ['__method' => 'delete']),
                '{"delete":{"_index":"nd_requests-2023-01-w05","_id":"e22161e2ecb9d885123cef67884e8502","_type":"core_requests"}}'. PHP_EOL,
                null,
            ],
            [
                'es2',
                array_merge($data, ['__method' => 'delete']),
                '{"delete":{"_index":"nd_requests-2023-01-w05","_id":"e22161e2ecb9d885123cef67884e8502","_type":"core_requests"}}'. PHP_EOL,
                null,
            ],
            [
                'es6',
                array_merge($data, ['__method' => 'delete']),
                '{"delete":{"_index":"nd_requests-2023-01-w05","_id":"e22161e2ecb9d885123cef67884e8502","_type":"_doc"}}'. PHP_EOL,
                null,
            ],
            [
                'es7',
                array_merge($data, ['__method' => 'delete']),
                '{"delete":{"_index":"nd_requests-2023-01-w05","_id":"e22161e2ecb9d885123cef67884e8502","_type":"_doc"}}'. PHP_EOL,
                null,
            ],
            [
                'es8',
                array_merge($data, ['__method' => 'delete']),
                '{"delete":{"_index":"nd_requests-2023-01-w05","_id":"e22161e2ecb9d885123cef67884e8502"}}'. PHP_EOL,
                null,
            ],
        ];
    }
}