<?php

namespace G4\Log\Data;

class TaskerEnd extends LoggerAbstract
{

    /**
     * @var string
     */
    private $type;

    /**
     * @return array
     */
    public function getRawData()
    {
        return [
            'id'                 => $this->getId(),
            'type'               => $this->type,
            'exec_time'          => $this->getElapsedTime(),
            'exec_time_ms'       => (int) ($this->getElapsedTime() * 1000),
            'php_version'        => str_replace(PHP_EXTRA_VERSION, '', PHP_VERSION),
            'app_version'        => $this->getAppVersionNumber(),
        ];
    }

    /**
     * @param $type string
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }
}
