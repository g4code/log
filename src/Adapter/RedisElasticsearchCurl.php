<?php

namespace G4\Log\Adapter;

use G4\ValueObject\Uuid;

class RedisElasticsearchCurl
{
    const TIMEOUT = 1;
    const METHOD_POST =   'POST';
    const BULK = '_bulk';
    const HTTP_CODE_200 = 200;

    /**
     * @var array
     */
    private $hosts;

    /**
     * @var array
     */
    private $versions;

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
     * RedisElasticsearchCurl constructor.
     *
     * @param array $hosts
     * @param string $index
     * @param string $type
     */
    public function __construct(array $hosts, array $versions=[])
    {
        $this->hosts = $this->buildHosts($hosts);
        $this->versions = array_values($versions);
    }

    public function getCountInfo()
    {
        $countInfo = '';
        if (!empty($this->counts)) {
            foreach ($this->counts as $host => $count ) {
                $countInfo .= sprintf("[log] |- ES Cluster: %s, count: %s, exec_time: %s ms\n", $host, $count['count'], number_format($count['exec_time']));
            }
        } else {
            foreach ($this->hosts as $host) {
                $countInfo .= sprintf("[log] |- ES Cluster: %s, count: 0\n", $host);
            }
        }

        return $countInfo;
    }

    public function sendAll(array $data)
    {
        foreach ($this->hosts as $hostId => $host) {
            $this->send(
                $this->buildBulkData($data, $hostId),
                $this->buildBulkUrl($host),
                self::METHOD_POST
            );
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
        $start = microtime(true);
        $response = curl_exec($ch);
        $duration = microtime(true) - $start;

        $info = curl_getinfo($ch);

        curl_close($ch);

        if (isset($info['http_code']) && ((int) $info['http_code'] < 200 || (int) $info['http_code'] > 299)) {
            throw new \Exception(
                sprintf(
                    "Unexpected response code:%s from ES has been returned on submit. More info: %s. Body: %s. Response: %s",
                    $info['http_code'],
                    json_encode($info),
                    json_encode($curlPostFieldsData),
                    is_array($response) ? json_encode($response) : $response
                )
            );
        }

        $data = json_decode($response,true);
        $host = substr($url, 0, strpos($url, '/'));
        $this->counts[$host] = [
            'count' => isset($data['items']) ? count($data['items']) : 0,
            'exec_time' => ceil($duration * 1000),
        ];
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

    public function isElasticsearchAvailable()
    {
        $host  = $this->hosts[array_rand(array_filter($this->hosts))];
        $ch = curl_init($host);

        curl_setopt_array($ch, [
            CURLOPT_TIMEOUT => self::TIMEOUT,
            CURLOPT_RETURNTRANSFER => true,
        ]);

        curl_exec($ch);

        $httpcode = (int) json_decode(curl_getinfo($ch, CURLINFO_HTTP_CODE));

        curl_close($ch);

        return $httpcode === self::HTTP_CODE_200;
    }

    private function buildBulkData(array $data, $hostId)
    {
        // es6 version has some breaking changes
        $esVersion6 = array_key_exists($hostId, $this->versions) && $this->versions[$hostId] === 'es6';

        $bulkData = [];
        foreach ($data as $log) {
            $this->setIndex($log['_index']);
            $this->setType($log['_type']);
            unset($log['_index'], $log['_type']);

            $bulkData[] = json_encode([
                'index' => [
                    '_index' => $this->index,
                    '_type'  => $esVersion6 ? '_doc': $this->type,
                    '_id'    => isset($log['id']) ? $log['id'] : (string) Uuid::generate()
                ]
            ]);

            $bulkData[] = $esVersion6
                ? json_encode(['index_type' => $this->type] + $log)
                : json_encode($log);
        }
        return implode(PHP_EOL, $bulkData) . PHP_EOL;
    }
}