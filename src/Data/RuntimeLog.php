<?php

namespace G4\Log\Data;

class RuntimeLog extends LoggerAbstract
{
    /**
     * @var mixed
     */
    private $loggedData;

    /**
     * @var string
     */
    private $tag;

    /**
     * @var integer
     */
    private $index;

    /**
     * @param mixed $var
     * @param string $tag
     * @param int $index
     */
    public function __construct($var, $tag, $index = 2)
    {
        $this->loggedData = $var;
        $this->tag        = $tag;
        $this->index      = $index;
    }

    /**
     * @return array
     */
    public function getRawData()
    {
        $trace  = \debug_backtrace();
        $line   = isset($trace[$this->index]['line']) ? $trace[$this->index]['line'] : null;
        $file   = isset($trace[$this->index]['file']) ? $trace[$this->index]['file'] : null;

        return [
            'id'        => $this->getId(),
            'timestamp' => $this->getJsTimestamp(),
            'datetime'  => \date('Y-m-d H:i:s'),
            'ip'        => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'cli',
            'file'      => $file,
            'line'      => $line,
            'data'      => \var_export($this->loggedData, true),
            'tag'       => $this->tag ? $this->tag : '',
            'client_ip' => $this->getClientIp(),
            'app_name'  => $this->getAppName(),
            'headers'   => \json_encode($this->getXNDParameters()),
            'uuid'      => $this->getUuid(),
        ];
    }
}
