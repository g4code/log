<?php

namespace G4\Log\Adapter;

use G4\Log\AdapterInterface;

class ElasticsearchCurl implements AdapterInterface
{

    const TIMEOUT = 1;

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

    public function save(array $data)
    {
        $this->send($data, $this->buildUrl($data['id']));
    }

    public function saveAppend(array $data)
    {
        $this->send(['doc' => $data], $this->buildUrl($data['id'], '_update'));
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

    private function send(array $data, $url)
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST  => 'POST',
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