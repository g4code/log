<?php

namespace G4\Log\Buffer;

use G4\Log\Adapter\ElasticsearchCurl;
use G4\Log\Adapter\Redis;
use G4\ValueObject\IntegerNumber;

class RedisToElastic
{

    /**
     * @var IntegerNumber
     */
    private $batchsize;

    /**
     * @var ElasticsearchCurl
     */
    private $elasticClient;

    /**
     * @var \Redis
     */
    private $redisClient;

    public function __construct(Redis $redisClient, ElasticsearchCurl $elasticClient, IntegerNumber $batchsize)
    {
        $this->redisClient      = $redisClient;
        $this->elasticClient    = $elasticClient;
        $this->batchsize        = $batchsize;
    }

    public function transferData()
    {

    }
}