<?php

namespace G4\Log;


class ErrorLogger
{
    /**
     * @var \G4\Log\Logger
     */
    private $logger;

    /**
     * \G4\Log\Logger should be instance of Error Logger
     * @param Logger $logger
     */
    public function __construct(\G4\Log\Logger $logger)
    {
        $this->logger = $logger;
    }

    public function log(\Exception $exception)
    {
        $errorData = new \G4\Log\Error\ErrorData();
        $errorData
            ->setTrace($exception->getTrace())
            ->setCode($exception->getCode())
            ->setMessage($exception->getMessage())
            ->setFile($exception->getFile())
            ->setLine($exception->getLine())
            ->thisIsException();

        $error = new \G4\Log\Data\Error();
        $error->setErrorData($errorData);

        $this->logger->log($error);
    }

}
