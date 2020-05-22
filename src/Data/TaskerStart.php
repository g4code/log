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
        return [
            'id'        => $this->getId(),
            'timestamp' => $this->getJsTimestamp(),
            'datetime'  => \date('Y-m-d H:i:s'),
            'options'   => \json_encode($this->options),
            'hostname'  => \gethostname(),
            'pid'       => \getmypid(),
            'php_version' => PHP_VERSION,
        ];
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
