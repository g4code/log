<?php

namespace G4\Log\Error;

class Exception extends ErrorAbstract
{

    /**
     * @param \Throwable $exception
     */
    public function handle(\Throwable $exception)
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
     * @param \Throwable $exception
     * @return array
     */
    private function getTrace(\Throwable $exception)
    {
        $e = $exception;
        while ($e->getPrevious() !== null) {
            $e = $e->getPrevious();
        }
        return $e->getTrace();
    }
}
