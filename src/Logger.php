<?php

namespace G4\Log;

class Logger
{

    const DEFAULT_LINE = 2;

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
        $this->adapter->save($data->getRawData());
    }

    public function logAppend(\G4\Profiler\Data\LoggerAbstract $data)
    {
        $this->adapter->saveAppend($data->getRawData());
    }

    public function runtimeLog($var, $tag = false, $index = self::DEFAULT_LINE)
    {
        $this->log(new \G4\Profiler\Data\RuntimeLog($var, $tag, $index));
    }

    public function logSecurity(\G4\Profiler\Data\LoggerAbstract $data)
    {
        $rawData = $data->getRawData();
        if (in_array($rawData['code'], [401, 403])) {
            $this->adapter->save($data->getRawData());
        }
    }
}