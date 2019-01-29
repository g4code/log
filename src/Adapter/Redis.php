<?php

namespace G4\Log\Adapter;

use G4\Log\AdapterAbstract;
use G4\ValueObject\IntegerNumber;
use G4\ValueObject\StringLiteral;

class Redis extends AdapterAbstract
{

    /**
     * @var \Redis
     */
    private $client;

    /**
     * @var StringLiteral
     */
    private $key;

    /**
     * Redis constructor.
     * @param \Redis $client
     */
    public function __construct(\Redis $client, StringLiteral $key)
    {
        $this->client   = $client;
        $this->key      = $key;
    }

    /**
     * @param IntegerNumber $batchsize
     * @return array
     */
    public function fetchAndClear(IntegerNumber $batchsize)
    {
        $data = $this->client->lRange((string) $this->key, 0, $batchsize->getValue());
        $this->client->lTrim((string) $this->key, $batchsize->getValue() + 1, -1);

        return $data;
    }

    public function save(array $data)
    {
        try {
            $this->shouldSaveInOneCall()
                ? $this->appendData($data)
                : $this->appendData($data)->doRPush();
        } catch (\Exception $exception) {
            error_log ($exception->getMessage(), 0);
        }

    }

    public function saveAppend(array $data)
    {
        try {
            $this->shouldSaveInOneCall()
                ? $this->appendData($data)->doRPush()
                : $this->doRPush($data);
        } catch (\Exception $exception) {
            error_log ($exception->getMessage(), 0);
        }

    }

    private function doRPush($data = null)
    {
        $logData = !empty($data) ? $data : $this->getData();
        $this->client->rPush((string) $this->key, \json_encode($logData));
    }

}