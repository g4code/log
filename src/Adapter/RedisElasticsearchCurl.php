<?php

namespace G4\Log\Adapter;

use G4\Log\AdapterAbstract;

class RedisElasticsearchCurl  extends AdapterAbstract
{
    const TIMEOUT = 1;
    const METHOD_POST =   'POST';
    const BULK = '_bulk';

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
    public function __construct(array $hosts)
    {
        $this->host     = $hosts[array_rand(array_filter($hosts))];
    }

    public function save(array $data)
    {
        $this->shouldSaveInOneCall()
            ? $this->appendData($data)
            : $this->send($data, $this->buildUrl($data['id']), self::METHOD_POST);
    }

    public function saveAppend(array $data)
    {
        $this->shouldSaveInOneCall()
            ? $this->appendData($data)->send($this->getData(), $this->buildUrl($data['id']), self::METHOD_POST)
            : $this->send(['doc' => $data], $this->buildUrl($data['id'], '_update'), self::METHOD_POST);
    }

    public function sendAll(array $data)
    {
        $itemsForBulkInsert = [];
        foreach ($data as $log) {
            $this->setIndex($log['_index']);
            $this->setType($log['_type']);
            unset($log['_index']);
            unset($log['_type']);

            $itemsForBulkInsert[] = json_encode([
                'index' => [
                    '_index' => $this->index,
                    '_type'  => $this->type,
                    '_id'    => $log['id']
                ]
            ]);
            $itemsForBulkInsert[] = json_encode($log);
        }
        $this->send(implode(PHP_EOL, $itemsForBulkInsert) . PHP_EOL, $this->buildBulkUrl(), self::METHOD_POST);
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

    private function buildBulkUrl()
    {
        return join('/', [
            $this->host,
            self::BULK
        ]);
    }


    private function send($data, $url, $method)
    {
        $curlPostFieldsData = $data;
        if(is_array($data)){
            $curlPostFieldsData = json_encode($data);
        }
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_POSTFIELDS     => $curlPostFieldsData,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_TIMEOUT        => self::TIMEOUT,
            CURLOPT_URL            => $url,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        ]);
        curl_exec($ch);
        curl_close($ch);
    }

    public function setIndex($index)
    {
        $this->index = $index;
        return $this;
    }

    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }
}