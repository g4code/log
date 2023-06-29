<?php

namespace G4\Log;

use G4\Log\Data\Exclude;
use G4\Version\Version;

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
     * @var Version|null
     */
    private $version;

    /**
     * Logger constructor.
     * @param AdapterInterface $adapter
     * @param Exclude|null $exclude
     * @param Version|null $version
     */
    public function __construct(\G4\Log\AdapterInterface $adapter, Exclude $exclude = null, Version $version = null)
    {
        $this->adapter = $adapter;
        $this->exclude = empty($exclude) ? new Exclude() : $exclude;
        $this->version = $version;
    }

    public function log(\G4\Log\Data\LoggerAbstract $data)
    {
        $data->setExcluded($this->exclude);
        $data->setAppVersionNumber($this->version);
        $this->adapter->save($data->getRawData());
    }

    public function logAppend(\G4\Log\Data\LoggerAbstract $data)
    {
        $data->setExcluded($this->exclude);
        $data->setAppVersionNumber($this->version);
        $this->adapter->saveAppend($data->getRawData());
    }

    public function runtimeLog($var, $tag = false, $index = self::DEFAULT_LINE)
    {
        $data = new \G4\Log\Data\RuntimeLog($var, $tag, $index);
        $data->setId(\md5(\uniqid(microtime(), true)));
        $this->log($data);
    }

    public function messageLog($mapperInterface, $queueName, $tag = false)
    {
        $data = new \G4\Log\Data\Messages($mapperInterface, $queueName, $tag);
        $data->setId(\md5(\uniqid(microtime(), true)));
        $data->setAppVersionNumber($this->version);
        if($data->isSourceAllowed()) {
            $this->log($data);
        }
    }
}
