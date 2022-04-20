<?php

namespace G4\Log\Data;

use G4\Log\Mapper\MessagesMapInterface;

class Messages extends LoggerAbstract
{
    /**
     * @var string
     */
    private $tag;
    
    /**
     * @var string
     */
    private $queue;

    /**
     * @var MessagesMapInterface
     */
    private $mapper;

    /**
     * @param MessagesMapInterface $mapperInterface
     * @param string $queue
     * @param string $tag
     */
    public function __construct($mapperInterface, $queue, $tag)
    {
        $this->mapper       = $mapperInterface;
        $this->queue        = $queue;
        $this->tag          = $tag;
    }
    
    /**
     * @return bool
     */
    public function isSourceAllowed()
    {
        return $this->mapper->isSourceAllowed();
    }
    
    /**
     * @return array
     */
    public function getRawData()
    {
        return array_merge([
            'id'            => $this->getId(),
            'timestamp'     => $this->getJsTimestamp(),
            'datetime'      => \date('Y-m-d H:i:s'),
            'ip'            => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'cli',
            'source'        => $this->mapper->getSource(),
            'queue'         => $this->queue,
            'user_sender'   => $this->mapper->getUserSender(),
            'user_receiver' => $this->mapper->getUserReceiver(),
            'data'          => $this->mapper->getMessage(),
            'tag'           => $this->tag ? $this->tag : '',
            'client_ip'     => $this->getClientIp(),
            'app_name'      => $this->getAppName(),
            'headers'       => \json_encode($this->getXNDParameters()),
            'uuid'          => $this->getUuid(),
            'php_version'   => str_replace(PHP_EXTRA_VERSION, '', PHP_VERSION),
            'hostname'      => \gethostname(),
        ], $this->getAdditionLogInformation());
    }
}
