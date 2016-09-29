<?php

namespace G4\Log;

abstract class AdapterAbstract implements AdapterInterface
{

    /**
     * @var array
     */
    private $data = [];

    /**
     * @var bool
     */
    private $shouldBeLazy = false;

    /**
     * @var bool
     */
    private $shouldSaveInOneCall = false;


    public function appendData(array $data)
    {
        $this->data += $data;
        return $this;
    }

    public function beLazy()
    {
        $this->shouldBeLazy = true;
    }

    public function getData()
    {
        return $this->data;
    }

    public function saveInOneCall()
    {
        $this->shouldSaveInOneCall = true;
    }

    public function shouldBeLazy()
    {
        return $this->shouldBeLazy;
    }

    public function shouldSaveInOneCall()
    {
        return $this->shouldSaveInOneCall;
    }
}