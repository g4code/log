<?php

namespace G4\Log\Error;

class Error extends ErrorAbstract
{

    public function handle($errno, $errstr, $errfile, $errline)
    {
        $this->getErrorData()
            ->setCode($errno)
            ->setMessage($errstr)
            ->setFile($this->filterFilePath($errfile))
            ->setLine($errline);
        $this
            ->display()
            ->log();
    }
}