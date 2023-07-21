<?php

namespace G4\Log\Error;

class ErrorData
{

    /**
     * @var int
     */
    private $code;

    /**
     * @var string
     */
    private $file;

    /**
     * @var bool
     */
    private $exceptionFlag;

    /**
     * @var int
     */
    private $line;

    /**
     * @var string
     */
    private $message;

    /**
     * @var array
     */
    private $trace;


    /**
     * @return array
     */
    public function getContext()
    {
        return [
            'REQUEST' => $_REQUEST,
            'SERVER'  => $_SERVER,
        ];
    }

    /**
     * @return string
     */
    public function getDataAsString()
    {
        return \join(PHP_EOL, [
            \strtoupper($this->getName()) . ": {$this->getMessage()}",
            "LINE: {$this->getLine()}",
            "FILE: {$this->getFile()}",
        ]) . PHP_EOL;
    }

    /**
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @return string
     */
    public function getLine()
    {
        return $this->line;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->exceptionFlag === true
            ? 'exception'
            : ErrorCodes::getName($this->getCode());
    }

    /**
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return array
     */
    public function getTrace()
    {
        return empty($this->trace)
            ? \debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)
            : $this->trace;
    }

    /**
     * @param $code int
     * @return $this
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @param $file string
     * @return $this
     */
    public function setFile($file)
    {
        $this->file = $file;
        return $this;
    }

    /**
     * @param $line int
     * @return $this
     */
    public function setLine($line)
    {
        $this->line = $line;
        return $this;
    }

    /**
     * @param $message string
     * @return $this
     */
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * @param $trace array
     * @return $this
     */
    public function setTrace($trace)
    {
        $this->trace = $trace;
        return $this;
    }

    /**
     * @return $this
     */
    public function thisIsException()
    {
        $this->exceptionFlag = true;
        return $this;
    }

    /**
     * @return bool
     */
    public function isException()
    {
        return $this->exceptionFlag;
    }
}
