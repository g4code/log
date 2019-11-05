<?php

namespace G4\Log\Error;

class Exception extends ErrorAbstract
{

    /**
     * @param \Exception|\Throwable $exception
     */
    public function handle($exception)
    {
        $this->getErrorData()
            ->setCode($exception->getCode())
            ->setMessage($exception->getMessage())
            ->setFile($this->filterFilePath($exception->getFile()))
            ->setLine($exception->getLine())
            ->setTrace($this->getTrace($exception))
            ->thisIsException();
        $this
            ->display()
            ->log();
    }

    /**
     * @param \Exception|\Throwable $exception
     * @return array
     */
    private function getTrace($exception)
    {
        $e = $exception;
        while ($e->getPrevious() !== null) {
            $e = $e->getPrevious();
        }
        return $e->getTrace();
    }
}
