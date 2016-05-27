<?php

namespace G4\Log\Adapter;

use G4\Log\AdapterInterface;
use Elasticsearch\ClientBuilder;
use Elasticsearch\Client;

class Elasticsearch implements AdapterInterface
{

    /**
     * Number of retries if first request fails
     */
    const RETRIES = 0;

    /**
     * Number of seconds for client-side, curl timeouts
     */
    const TIMEOUT = 1;


    /**
     * @var Client
     */
    private $client;

    /**
     * @var string
     */
    private $index;

    /**
     * @var string
     */
    private $type;

    /**
     * Elasticsearch constructor.
     * @param array $hosts
     * @param string $index
     * @param string $type
     */
    public function __construct(array $hosts, $index, $type)
    {
        $this->index  = $index;
        $this->type   = $type;
        $this->client = ClientBuilder::create()
            ->setHosts($hosts)
            ->setRetries(self::RETRIES)
            ->build();
    }

    public function save($data)
    {
        try {
            $this->client->index($this->prepareForIndexing($data->getRawData()));
        } catch (\Exception $exception) {
            error_log ($exception->getMessage(), 0);
        }
    }

    public function saveAppend($data)
    {
        try {
            $this->client->update($this->prepareForUpdate($data->getRawData()));
        } catch (\Exception $exception) {
            error_log ($exception->getMessage(), 0);
        }
    }

    private function prepareForIndexing(array $data)
    {
        return [
            'index' => $this->index,
            'type'  => $this->type,
            'id'    => $data['id'],
            'body'  => $data,
            'client' => [
                'timeout'         => self::TIMEOUT,
                'connect_timeout' => self::TIMEOUT,
            ],
        ];
    }

    private function prepareForUpdate(array $data)
    {
        return [
            'index' => $this->index,
            'type'  => $this->type,
            'id'    => $data['id'],
            'body'  => [
                'doc' => $data,
            ],
            'client' => [
                'timeout'         => self::TIMEOUT,
                'connect_timeout' => self::TIMEOUT,
            ],
        ];
    }
}
