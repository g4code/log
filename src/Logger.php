<?php

namespace G4\Log;

class Logger
{

    /**
     * @var AdapterInterface
     */
    private $adapter;

    /**
     * Logger constructor.
     * @param AdapterInterface $adapter
     */
    public function __construct(\G4\Log\AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    public function log(\G4\Profiler\Data\LoggerAbstract $data)
    {
        $this->adapter->save($data);
    }

    public function logAppend(\G4\Profiler\Data\LoggerAbstract $data)
    {
        $this->adapter->saveAppend($data);
    }
}