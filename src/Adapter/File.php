<?php

namespace G4\Log\Adapter;

use G4\Log\AdapterInterface;

class File implements AdapterInterface
{

    /**
     * @var string
     */
    private $filename;

    /**
     * File constructor.
     * @param $filename
     */
    public function __construct($filename)
    {
        $this->filename = $filename;
    }

    /**
     * @param $data
     */
    public function save($data)
    {
        error_log("\n" . $this->format($data->getRawData()) . "\n", 3, $this->filename);
    }

    /**
     * @param $data
     */
    public function saveAppend($data)
    {
        error_log($this->format($data->getRawData()) . "\n", 3, $this->filename);
    }

    /**
     * @param array $rawData
     * @return string
     */
    private function format(array $rawData)
    {
        array_walk($rawData, function(&$value, $key) {
            $value = str_pad($key . ": ", 15) . $value;
        });
        return implode($rawData,"\n");
    }

}