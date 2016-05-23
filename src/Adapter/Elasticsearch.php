<?php

namespace G4\Log\Adapter;

use G4\Log\AdapterInterface;
use Elasticsearch\ClientBuilder;
use Elasticsearch\Client;

class Elasticsearch implements AdapterInterface
{

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
        $this->client = ClientBuilder::create()->setHosts($hosts)->build();
        $this->index  = $index;
        $this->type   = $type;
    }

    public function save($data)
    {
        try {
            $this->client->index($this->prepareForIndexing($data->getRawData()));
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), $e->getCode());
        }
    }

    public function saveAppend($data)
    {
        try {
            $this->client->update($this->prepareForUpdate($data->getRawData()));
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), $e->getCode());
        }
    }

    private function prepareForIndexing(array $data)
    {
        return [
            'index' => $this->index,
            'type'  => $this->type,
            'id'    => $data['id'],
            'body'  => $data,
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
        ];
    }
}
