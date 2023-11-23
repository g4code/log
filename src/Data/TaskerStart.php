<?php

namespace G4\Log\Data;

class TaskerStart extends LoggerAbstract
{

    /**
     * @var array
     */
    private $options;

    /**
     * @return array
     */
    public function getRawData()
    {
        $rawData = [
            'id' => $this->getId(),
            'timestamp' => $this->getJsTimestamp(),
            'datetime' => \date('Y-m-d H:i:s'),
            'options' => \json_encode($this->options),
            'hostname' => \gethostname(),
            'pid' => \getmypid(),
            'php_version' => str_replace(PHP_EXTRA_VERSION, '', PHP_VERSION),
            'app_version' => $this->getAppVersionNumber(),
        ];

        $rawData += $this->getCpuLoad();

        return $rawData;
    }

    /**
     * @param $options array
     * @return $this
     */
    public function setOptions($options)
    {
        $this->options = $options;
        return $this;
    }
}
