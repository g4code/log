<?php

namespace G4\Log\Buffer;

use G4\Log\Adapter\ElasticsearchCurl;

class RedisToElastic
{

    private $elasticClient;

    private $redisClient;

    public function __construct(\Redis $redisClient, ElasticsearchCurl $elasticClient)
    {
        $this->redisClient      = $redisClient;
        $this->elasticClient    = $elasticClient;
    }

    public function transferData()
    {

    }
}