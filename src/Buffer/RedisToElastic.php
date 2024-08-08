<?php

namespace G4\Log\Buffer;

use G4\Log\Adapter\RedisElasticsearchCurl;
use G4\Log\Adapter\Redis;
use G4\Log\Error\Exception;
use G4\ValueObject\IntegerNumber;

class RedisToElastic
{
    const LOG_TITLE = '[log]';

    /**
     * @var IntegerNumber
     */
    private $batchsize;

    /**
     * @var RedisElasticsearchCurl
     */
    private $elasticClient;

    /**
     * @var \Redis
     */
    private $redisClient;

    /**
     * @var array
     */
    private $data;

    /**
     * @var int
     */
    private $countFromRedis;

    /**
     * @var array
     */
    private $prettyPrintFields;

    private $truncatedFields;

    /**
     * RedisToElastic constructor.
     *
     * @param Redis $redisClient
     * @param RedisElasticsearchCurl $elasticClient
     * @param IntegerNumber $batchsize
     */
    public function __construct(Redis $redisClient, RedisElasticsearchCurl $elasticClient, IntegerNumber $batchsize)
    {
        $this->redisClient      = $redisClient;
        $this->elasticClient    = $elasticClient;
        $this->batchsize        = $batchsize;
        $this->prettyPrintFields = [];
        $this->truncatedFields = [];
    }

    public function setPrettyPrintFields(array $fields)
    {
        $this->prettyPrintFields = $fields;
        return $this;
    }

    public function setTruncatedFields(array $fields)
    {
        $this->truncatedFields = $fields;
        return $this;
    }

    public function transferData()
    {
        $data = $this->redisClient->fetchAndClear($this->batchsize);
        $this->countFromRedis = count($data);
        if (!empty($data)) {
            foreach ($data as $key => $log) {
                $docLength = strlen($log);
                $logData = json_decode($log, 1);
                $prettyLogData = $this->prettify($logData);
                $this->data[$key] = $this->truncateLongFields($prettyLogData);
                $this->data[$key]['doc_length'] = $docLength;
                $this->data[$key]['doc_length_real'] = strlen(json_encode($this->data[$key]));
            }
        }

        return $this;
    }

    private function prettify(array $logData)
    {
        return (new PrettifyJson($this->redisClient->getKey(), $this->prettyPrintFields))
            ->prettify($logData);
    }

    private function truncateLongFields(array $logData)
    {
        return (new FieldsTruncator($this->redisClient->getKey(), $this->truncatedFields))
            ->truncate($logData);
    }

    public function insertIntoES()
    {
        if (empty($this->data)) {
            return $this;
        }

        try {
            $this->elasticClient->sendAll($this->data);
        } catch (\Exception $exception) {
            error_log ($exception->getMessage(), 0);
            $this->rollbackToRedis();
        }

        return $this;
    }

    public function getInfo()
    {
        echo self::LOG_TITLE . ' The number of ' . (string) $this->redisClient->getKey()
            . ' logs read from redis: ' . $this->countFromRedis . PHP_EOL;
        echo self::LOG_TITLE . ' The number of ' . (string) $this->redisClient->getKey()
            . ' logs inserted in ES: ' . PHP_EOL . $this->elasticClient->getCountInfo() . PHP_EOL;
        return $this;
    }

    private function rollbackToRedis()
    {
        $this->redisClient->doRPushBatch($this->data);

        echo self::LOG_TITLE . ' ES Cluster is not available at the moment.' . PHP_EOL;
    }
}
