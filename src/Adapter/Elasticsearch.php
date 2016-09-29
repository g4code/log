<?php

namespace G4\Log\Adapter;

use G4\Log\AdapterAbstract;
use Elasticsearch\ClientBuilder;
use Elasticsearch\Client;

class Elasticsearch extends AdapterAbstract
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
        $this->index = $index;
        $this->type  = $type;

        $this->client = ClientBuilder::create()
            ->setHosts($hosts)
            ->setRetries(self::RETRIES)
            ->build();

    }

    public function save(array $data)
    {
        try {
            $this->shouldSaveInOneCall()
                ? $this->appendData($data)
                : $this->doIndexing($data);
        } catch (\Exception $exception) {
            error_log ($exception->getMessage(), 0);
        }
    }

    public function saveAppend(array $data)
    {
        try {
            $this->shouldSaveInOneCall()
                ? $this->appendData($data)->doIndexing($this->getData())
                : $this->doUpdate($data);
        } catch (\Exception $exception) {
            error_log ($exception->getMessage(), 0);
        }
    }

    private function doIndexing(array $data)
    {
        $this->client->index([
            'index' => $this->index,
            'type'  => $this->type,
            'id'    => $data['id'],
            'body'  => $data,
            'client' => $this->getClientOptions(),
        ]);
    }

    private function doUpdate(array $data)
    {
        $this->client->update([
            'index' => $this->index,
            'type'  => $this->type,
            'id'    => $data['id'],
            'body'  => [
                'doc' => $data,
            ],
            'client' => $this->getClientOptions(),
        ]);
    }

    private function getClientOptions()
    {
        $clientOptions = [
            'timeout'         => self::TIMEOUT,
            'connect_timeout' => self::TIMEOUT,
        ];
        if ($this->shouldBeLazy() && $this->shouldSaveInOneCall()) {
            $clientOptions['future'] = 'lazy';
        }
        return $clientOptions;
    }
}
