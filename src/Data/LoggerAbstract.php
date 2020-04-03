<?php

namespace G4\Log\Data;

use G4\Utility\Tools;

abstract class LoggerAbstract
{
    const HEADER_CLIENT_IP = 'HTTP_X_ND_CLIENT_IP';
    const HEADER_APP_NAME  = 'HTTP_X_ND_APP_NAME';
    const HEADER_UUID      = 'HTTP_X_ND_UUID';
    const X_ND_PREFIX      = 'X_ND';

    /**
     * @var int
     */
    private $id;

    /**
     * @var float
     */
    private $startTime;

    /**
     * @return float
     */
    public function getElapsedTime()
    {
        return \microtime(true) - $this->startTime;
    }

    /**
     * @param float $startTime
     * @return $this
     */
    public function setStartTime($startTime)
    {
        $this->startTime = $startTime;
        return $this;
    }
    /**
     * @param int $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * Return javascript timestamp with milliseconds
     * @return int
     */
    public function getJsTimestamp()
    {
        return (int) (\microtime(true) * 1000);
    }

    public function getClientIp()
    {
        $tools = new Tools();
        $clientIp = $tools->getRealIP(false, [self::HEADER_CLIENT_IP]);
        return $clientIp ?: null;
    }

    public function getAppName()
    {
        return \array_key_exists(self::HEADER_APP_NAME, $_SERVER) ? $_SERVER[self::HEADER_APP_NAME] : null;
    }

    /**
     * @return array
     */
    public function getXNDParameters()
    {
        return \array_filter($_SERVER, function($key) {
            return \strpos($key, self::X_ND_PREFIX) !== false;
        },ARRAY_FILTER_USE_KEY);
    }

    public function getUuid()
    {
        return \array_key_exists(self::HEADER_UUID, $_SERVER) ? $_SERVER[self::HEADER_UUID] : null;
    }

    public function getAdditionLogInformation()
    {
        return is_callable(['App\DI', 'getAdditionalLog']) ? \App\DI::getAdditionalLog()->getInformation() : [];
    }
}
