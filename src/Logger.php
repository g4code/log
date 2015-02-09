<?php

namespace G4\Log;

class Logger
{


    public function __construct(\G4\Log\Adapter\Solr $adapter)
    {
        $this->adapter = $adapter;
    }

    public function log(\G4\Profiler\Data\LoggerAbstract $data)
    {
        $this->adapter
            ->setDomain($data)
            ->save();
    }

    public function logAppend(\G4\Profiler\Data\LoggerAbstract $data)
    {
        $this->adapter
            ->setDomain($data)
            ->saveAppend();
    }
}