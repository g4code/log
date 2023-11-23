<?php

namespace G4\Log\Adapter;

use G4\Log\Consts\RedisToElasticsearchConstants as Consts;
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
            if (!isset($log[Consts::_INDEX], $log[Consts::_TYPE]))
            {
                var_dump("Undefined _index or _type", $log);
                continue;
            }

            $index = $log[Consts::_INDEX];
            $type = $log[Consts::_TYPE];
            $id = isset($log[Consts::ID]) ? $log[Consts::ID] : (string)Uuid::generate();
            $method = isset($log[Consts::__METHOD]) ? $log[Consts::__METHOD] : Consts::METHOD_INDEX; //possible values: index/create/delete/update

            unset($log[Consts::_INDEX], $log[Consts::_TYPE], $log[Consts::__METHOD]);

            $header = [
                        Consts::_INDEX => $index,
                        Consts::_ID => $id
                    ];

            $body = null;

            switch (true) {
                case in_array($esVersion, [Consts::ES6, Consts::ES7]):
                    $header[Consts::_TYPE] = Consts::_DOC;
                    $body = [Consts::INDEX_TYPE => $type] + $log;
                    break;
                case $esVersion === Consts::ES8:
                    $body = [Consts::INDEX_TYPE => $type] + $log;
                    break;
                default:    //before es6
                    $header[Consts::_TYPE] = $type;
                    $body = $log;
                    break;
            }

            $bulkData[] = json_encode([$method => $header]);

            $bodyWithMethod = self::getBodyWithMethod($method, $body);
            if($bodyWithMethod) {
                $bulkData[] = $bodyWithMethod;
            }
        }
        return implode(PHP_EOL, $bulkData) . PHP_EOL;
    }

    private static function getBodyWithMethod($method, $body)
    {
        switch ($method) {
            case Consts::METHOD_DELETE:
                return null;
            case Consts::METHOD_UPDATE:
                return json_encode([Consts::METHOD_UPDATE_WRAP => $body]);
            case Consts::METHOD_INDEX:
            case Consts::METHOD_CREATE:
            default:
                return json_encode($body);
        }
    }
}