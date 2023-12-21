<?php

namespace G4\Log;

use G4\Log\Data\Exclude;
use G4\Log\Mapper\MessagesMapInterface;
use G4\Version\Version;

/**
 *
 */
class Logger
{

    const DEFAULT_LINE = 2;

    const INDEX_LEVEL_THREE = 3;

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

    /**
     * @param Data\LoggerAbstract $data
     * @param string $logLevel
     * @return void
     */
    public function log(\G4\Log\Data\LoggerAbstract $data, $logLevel = LoggingLevels::DEBUG)
    {

        $data->setExcluded($this->exclude);
        $data->setAppVersionNumber($this->version);
        $data->setLogLevel($logLevel);
        $this->adapter->save($data->getRawData());
    }

    /**
     * @param Data\LoggerAbstract $data
     * @return void
     */
    public function logAppend(\G4\Log\Data\LoggerAbstract $data)
    {
        $data->setExcluded($this->exclude);
        $data->setAppVersionNumber($this->version);
        $this->adapter->saveAppend($data->getRawData());
    }

    /**
     * @param mixed $var
     * @param string $tag
     * @param int $index
     * @param string $logLevel
     * @return void
     */
    public function runtimeLog($var, $tag = false, $index = self::DEFAULT_LINE, $logLevel = LoggingLevels::DEBUG)
    {
        $data = new \G4\Log\Data\RuntimeLog($var, $tag, $index);
        $data->setId(\md5(\uniqid(microtime(), true)));
        $this->log($data, $logLevel);
    }

    /**
     * @param MessagesMapInterface $mapperInterface
     * @param string $queueName
     * @param string $tag
     * @return void
     */
    public function messageLog($mapperInterface, $queueName, $tag = false)
    {
        $data = new \G4\Log\Data\Messages($mapperInterface, $queueName, $tag);
        $data->setId(\md5(\uniqid(microtime(), true)));
        $data->setAppVersionNumber($this->version);
        if ($data->isSourceAllowed()) {
            $this->log($data);
        }
    }

    /**
     * @param mixed $var
     * @param string $tag
     * @param int $index
     * @return void
     */
    public function emergency($var, $tag = false, $index = self::INDEX_LEVEL_THREE)
    {
        $this->runtimeLog($var, $tag, $index, LoggingLevels::EMERGENCY);
    }

    /**
     * @param mixed $var
     * @param string $tag
     * @param int $index
     * @return void
     */
    public function alert($var, $tag = false, $index = self::INDEX_LEVEL_THREE)
    {
        $this->runtimeLog($var, $tag, $index, LoggingLevels::ALERT);
    }

    /**
     * @param mixed $var
     * @param string $tag
     * @param int $index
     * @return void
     */
    public function critical($var, $tag = false, $index = self::INDEX_LEVEL_THREE)
    {
        $this->runtimeLog($var, $tag, $index, LoggingLevels::CRITICAL);
    }

    /**
     * @param mixed $var
     * @param string $tag
     * @param int $index
     * @return void
     */
    public function error($var, $tag = false, $index = self::INDEX_LEVEL_THREE)
    {
        $this->runtimeLog($var, $tag, $index, LoggingLevels::ERROR);
    }

    /**
     * @param mixed $var
     * @param string $tag
     * @param int $index
     * @return void
     */
    public function warning($var, $tag = false, $index = self::INDEX_LEVEL_THREE)
    {
        $this->runtimeLog($var, $tag, $index, LoggingLevels::WARNING);
    }

    /**
     * @param mixed $var
     * @param string $tag
     * @param int $index
     * @return void
     */
    public function notice($var, $tag = false, $index = self::INDEX_LEVEL_THREE)
    {
        $this->runtimeLog($var, $tag, $index, LoggingLevels::NOTICE);
    }

    /**
     * @param mixed $var
     * @param string $tag
     * @param int $index
     * @return void
     */
    public function info($var, $tag = false, $index = self::INDEX_LEVEL_THREE)
    {
        $this->runtimeLog($var, $tag, $index, LoggingLevels::INFO);
    }
}
