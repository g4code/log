<?php

namespace G4\Log\Data;

use G4\Log\Error\ErrorData;

class Error extends LoggerAbstract
{

    /**
     * @var ErrorData
     */
    private $errorData;

    /**
     * @return array
     */
    public function getRawData()
    {
        return array_merge([
            'id'        => \md5(\uniqid(microtime(), true)),
            'timestamp' => $this->getJsTimestamp(),
            'datetime'  => \date('Y-m-d H:i:s'),

            'code'      => $this->errorData->getCode(),
            'type'      => $this->errorData->getName(),
            'message'   => $this->errorData->getMessage(),
            'file'      => $this->errorData->getFile(),
            'line'      => $this->errorData->getLine(),
            'trace'     => \json_encode($this->errorData->getTrace()),
            'context'   => \json_encode($this->errorData->getContext()),

            'hostname'  => \gethostname(),
            'pid'       => \getmypid(),
            'ip'        => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR']: 'cli',
            'client_ip' => $this->getClientIp(),
            'app_name'  => $this->getAppName(),
            'headers'   => \json_encode($this->getXNDParameters()),
            'uuid'      => $this->getUuid(),
            'php_version' => str_replace(PHP_EXTRA_VERSION, '', PHP_VERSION),
            'app_version' => $this->getAppVersionNumber(),
        ], $this->getAdditionLogInformation());
    }

    /**
     * @param ErrorData $errorData
     * @return $this
     */
    public function setErrorData(ErrorData $errorData)
    {
        $this->errorData = $errorData;
        return $this;
    }
}
