<?php

namespace G4\Log;

abstract class AdapterAbstract implements AdapterInterface
{

    private $data = [];

    /**
     * @var bool
     */
    private $shouldSaveInOneCall = false;


    public function appendData(array $data)
    {
        $this->data += $data;
        return $this;
    }

    public function getData()
    {
        return $this->data;
    }

    public function saveInOneCall()
    {
        $this->shouldSaveInOneCall = true;
    }

    public function shouldSaveInOneCall()
    {
        return $this->shouldSaveInOneCall;
    }
}