<?php

namespace G4\Log\Error;

class Exception extends ErrorAbstract
{

    /**
     * @param \Exception $exception
     */
    public function handle(\Exception $exception)
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
     * @param \Exception $exception
     * @return array
     */
    private function getTrace(\Exception $exception)
    {
        $e = $exception;
        while ($e->getPrevious() !== null) {
            $e = $e->getPrevious();
        }
        return $e->getTrace();
    }
}
