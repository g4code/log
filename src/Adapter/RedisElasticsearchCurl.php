<?php

namespace G4\Log\Adapter;

use G4\ValueObject\Uuid;

class RedisElasticsearchCurl
{
    const TIMEOUT = 1;
    const METHOD_POST =   'POST';
    const BULK = '_bulk';


    /**
     * @var array
     */
    private $hosts;
    /**

    /**
     * @var string
     */
    private $index;

    /**
     * @var string
     */
    private $type;

    /**
     * @var array
     */
    private $counts;

    /**
     * Elasticsearch constructor.
     * @param array $hosts
     * @param string $index
     * @param string $type
     */
    public function __construct(array $hosts)
    {
        $this->hosts = $this->buildHosts($hosts);
    }

    public function getCountInfo()
    {
        $countInfo = '';
        if (!empty($this->counts)) {
            foreach ($this->counts as $key => $count ) {
                $countInfo .= '[log] ES Cluster: ' . $key . ', count: ' . $count  . PHP_EOL;
            }
        } else {
            foreach ($this->hosts as $host) {
                $countInfo .= '[log] ES Cluster: ' . $host . ', count: ' . 0  . PHP_EOL;
            }
        }

        return $countInfo;
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
                    '_id'    => isset($log['id']) ? $log['id'] : (string) Uuid::generate()
                ]
            ]);
            $itemsForBulkInsert[] = json_encode($log);
        }

        foreach ($this->hosts as $host) {
            $this->send(implode(PHP_EOL, $itemsForBulkInsert) . PHP_EOL, $this->buildBulkUrl($host), self::METHOD_POST);
        }
    }

    private function buildBulkUrl($host)
    {
        return join('/', [
            $host,
            self::BULK
        ]);
    }

    private function buildHosts(array $hosts)
    {
        $formattedHosts = [];
        foreach ($hosts as $host) {
            if (!empty(array_filter($host))) {
                $formattedHosts[] = $host[array_rand(array_filter($host))];
            }
        }
        return $formattedHosts;
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
        $response = curl_exec($ch);
        $data = json_decode($response,1);
        $host = substr($url, 0, strpos($url, '/'));
        $this->counts[$host] = isset($data['items']) ? count($data['items']) : 0;
        curl_close($ch);
    }

    private function setIndex($index)
    {
        $this->index = $index;
        return $this;
    }

    private function setType($type)
    {
        $this->type = $type;
        return $this;
    }


}