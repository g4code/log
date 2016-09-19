<?php

namespace G4\Log\Adapter;

use G4\Log\AdapterAbstract;

class File extends AdapterAbstract
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
    public function save(array $data)
    {
        $this->shouldSaveInOneCall()
            ? $this->appendData($data)
            : $this->errorLog("\n" . $this->format($data) . "\n");
    }

    /**
     * @param $data
     */
    public function saveAppend(array $data)
    {
        $this->shouldSaveInOneCall()
            ? $this->appendData($data)->errorLog("\n" . $this->format($this->getData()) . "\n")
            : $this->errorLog($this->format($data) . "\n");
    }

    private function errorLog($formattedData)
    {
        error_log($formattedData, 3, $this->filename);
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