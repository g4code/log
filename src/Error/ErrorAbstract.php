<?php

namespace G4\Log\Error;

use G4\Log\Logger;

abstract class ErrorAbstract
{

    /**
     * @var ErrorData
     */
    private $errorData;

    /**
     * @var bool
     */
    private $debug;

    /**
     * @var \G4\Log\Logger
     */
    private $logger;

    /**
     * @var string
     */
    private $pathRoot;


    public function __construct()
    {
    }

    /**
     * @return array
     */
    public function getBackTrace()
    {
        return \debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
    }

    /**
     * @return ErrorData
     */
    public function getErrorData()
    {
        if (!$this->errorData instanceof ErrorData) {
            $this->errorData = new ErrorData();
        }
        return $this->errorData;
    }

    /**
     * @param $debug bool
     * @return $this
     */
    public function setDebug($debug)
    {
        $this->debug = $debug;
        return $this;
    }

    /**
     * @param Logger $logger
     * @return $this
     */
    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * @param $pathRoot string
     * @return $this
     */
    public function setPathRoot($pathRoot)
    {
        $this->pathRoot = $pathRoot;
        return $this;
    }

    /**
     * @return $this
     */
    public function display()
    {
        if ($this->shouldDisplay()) {
            $presenter = new Presenter();
            $presenter
                ->setData($this->errorData)
                ->display();
        }
        $this->sendResponseHeader();
        return $this;
    }

    /**
     * @param $file string
     * @return mixed
     */
    public function filterFilePath($file)
    {
        return $this->pathRoot === null
            ? $file
            : str_replace(realpath($this->pathRoot), '', $file);
    }

    /**
     * @return $this
     */
    public function log()
    {
        if ($this->logger instanceof Logger) {
            $loggerData = new \G4\Log\Data\Error();
            $loggerData
                ->setErrorData($this->errorData);
            $this->logger->log($loggerData);
        }
        return $this;
    }

    /**
     * @return bool
     */
    private function shouldDisplay()
    {
        return $this->debug
            && error_reporting()
            && ($this->errorData->getCode() || $this->errorData->isException());
    }

    private function sendResponseHeader()
    {
        if (php_sapi_name() === 'cli' || headers_sent()) {
            return;
        }

        if (!$this->errorData->getCode() && $this->errorData->isException()) {
            $responseCode = 500;
        }

        if ($this->errorData->getCode()) {
            $responseCode = $this->errorData->getCode();
        }

        http_response_code($responseCode);
    }
}
