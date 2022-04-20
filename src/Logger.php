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

    public function log(\G4\Log\Data\LoggerAbstract $data)
    {
        $this->adapter->save($data->getRawData());
    }

    public function logAppend(\G4\Log\Data\LoggerAbstract $data)
    {
        $this->adapter->saveAppend($data->getRawData());
    }

    public function runtimeLog($var, $tag = false, $index = self::DEFAULT_LINE)
    {
        $this->log(new \G4\Log\Data\RuntimeLog($var, $tag, $index));
    }

    public function messageLog($mapperInterface, $queueName, $tag = false)
    {
        $data = new \G4\Log\Data\Messages($mapperInterface, $queueName, $tag);
        if($data->isSourceAllowed()) {
            $this->log($data);
        }
    }
}
