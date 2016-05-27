<?php

namespace G4\Log\Adapter;

use G4\Log\AdapterInterface;

class Solr implements AdapterInterface
{

    const TIMEOUT = 1;

    const IDENTIFIER_KEY = 'id';

    private $collection;

    private $host;

    private $url;


    /**
     * Solr constructor.
     * @param array $params
     */
    public function __construct($host, $collection)
    {
        $this->host       = $host;
        $this->collection = $collection;
    }

    public function save($data)
    {
        $this->send([$data->getRawData()]);
    }

    public function saveAppend($data)
    {
        $rawData = $data->getRawData();
        array_walk($rawData, function(&$value, $key){
            if ($key != self::IDENTIFIER_KEY) {
                $value = ['add' => $value];
            }
        });
        $this->send([$rawData]);
    }

    private function buildUrl()
    {
        if ($this->url === null) {
            $this->url = join('', [
                $this->host,
                '/solr/',
                $this->collection,
                '/update/',
            ]);
        }
        return $this->url;
    }

    private function send(array $data)
    {
        $ch = curl_init($this->buildUrl());
        curl_setopt_array($ch, [
            CURLOPT_POST           => 1,
            CURLOPT_POSTFIELDS     => json_encode($data),
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_TIMEOUT        => self::TIMEOUT,
            CURLOPT_URL            => $this->buildUrl(),
        ]);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_exec($ch);
        curl_close($ch);
    }
}