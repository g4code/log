<?php

namespace G4\Log\Buffer;

use G4\Log\Adapter\RedisElasticsearchCurl;
use G4\Log\Adapter\Redis;
use G4\ValueObject\IntegerNumber;

class RedisToElastic
{

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

    private $data;

    public function __construct(Redis $redisClient, RedisElasticsearchCurl $elasticClient, IntegerNumber $batchsize)
    {
        $this->redisClient      = $redisClient;
        $this->elasticClient    = $elasticClient;
        $this->batchsize        = $batchsize;
    }

    public function transferData()
    {
        $data = $this->redisClient->fetchAndClear($this->batchsize);
        if (!empty($data)) {
            foreach ($data as $key => $log) {
                $logData = json_decode($log, 1);
                $this->data[$key] = $logData;
            }
        }

        return $this;
    }

    public function insertIntoES()
    {
        if (!empty($this->data)) {
            $this->elasticClient->sendAll($this->data);
        }
    }
}