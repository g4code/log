<?php

namespace G4\Log\Adapter;

use G4\ValueObject\Uuid;

class RedisToEsBuildBulkData
{
    /**
     * @param array $data
     * @param string $esVersion
     */
    public static function buildBulkData($data, $esVersion)
    {
        $bulkData = [];
        foreach ($data as $log) {
            // skip invalid log entries
            if (!isset($log['_index'], $log['_type']))
            {
                var_dump("Undefined _index or _type", $log);
                continue;
            }

            $index = $log['_index'];
            $type = $log['_type'];
            unset($log['_index'], $log['_type']);

            switch (true) {
                case in_array($esVersion, ['es6', 'es7']):
                    $bulkData[] = json_encode([
                        'index' => [
                            '_index' => $index,
                            '_type' => '_doc',
                            '_id' => isset($log['id']) ? $log['id'] : (string)Uuid::generate()
                        ],
                    ]);
                    $bulkData[] = json_encode(['index_type' => $type] + $log);
                    break;
                case $esVersion === 'es8':
                    $bulkData[] = json_encode([
                        'index' => [
                            '_index' => $index,
                            '_id' => isset($log['id']) ? $log['id'] : (string)Uuid::generate()
                        ]
                    ]);
                    $bulkData[] = json_encode(['index_type' => $type] + $log);
                    break;
                default:    //before es6
                    $bulkData[] = json_encode([
                        'index' => [
                            '_index' => $index,
                            '_type' => $type,
                            '_id' => isset($log['id']) ? $log['id'] : (string)Uuid::generate()
                        ]
                    ]);
                    $bulkData[] = json_encode($log);
                    break;
            }

            ;
        }
        return implode(PHP_EOL, $bulkData) . PHP_EOL;
    }
}