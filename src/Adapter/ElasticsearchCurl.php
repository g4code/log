<?php

namespace G4\Log\Adapter;

use G4\Log\AdapterInterface;

class ElasticsearchCurl implements AdapterInterface
{

    const TIMEOUT = 1;
    const METHOD_DELETE = 'DELETE';
    const METHOD_POST =   'POST';


    /**
     * @var string
     */
    private $host;

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
        $this->index    = $index;
        $this->type     = $type;
        $this->host     = $hosts[array_rand(array_filter($hosts))];
    }

    public function deleteByQuery(array $data)
    {
        $this->send($data,  $this->buildUrl('_query'), self::METHOD_DELETE);
    }

    public function save(array $data)
    {
        $this->send($data, $this->buildUrl($data['id']), self::METHOD_POST);
    }

    public function saveAppend(array $data)
    {
        $this->send(['doc' => $data], $this->buildUrl($data['id'], '_update'), self::METHOD_POST);
    }

    private function buildUrl($id, $update = null)
    {
        return join('/', [
            $this->host,
            $this->index,
            $this->type,
            $id,
            $update
        ]);
    }

    private function send(array $data, $url, $method)
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_POSTFIELDS     => json_encode($data),
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_TIMEOUT        => self::TIMEOUT,
            CURLOPT_URL            => $url,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        ]);
        curl_exec($ch);
        curl_close($ch);
    }
}