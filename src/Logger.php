<?php

namespace G4\Log;

use G4\Log\Data\Exclude;

class Logger
{

    const DEFAULT_LINE = 2;

    /**
     * @var AdapterInterface
     */
    private $adapter;

    /** @var Exclude */
    private $exclude;

    /**
     * Logger constructor.
     * @param AdapterInterface $adapter
     * @param Exclude|null $exclude
     */
    public function __construct(\G4\Log\AdapterInterface $adapter, Exclude $exclude = null)
    {
        $this->adapter = $adapter;
        $this->exclude = empty($exclude) ? new Exclude() : $exclude;
    }

    public function log(\G4\Log\Data\LoggerAbstract $data)
    {
        $data->setExcluded($this->exclude);
        $this->adapter->save($data->getRawData());
    }

    public function logAppend(\G4\Log\Data\LoggerAbstract $data)
    {
        $data->setExcluded($this->exclude);
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
