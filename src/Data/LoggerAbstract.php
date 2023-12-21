<?php

namespace G4\Log\Data;

use G4\Constants\Override;
use G4\Utility\Tools;
use G4\Version\Version;

abstract class LoggerAbstract
{
    const HEADER_CLIENT_IP = 'HTTP_X_ND_CLIENT_IP';
    const HEADER_APP_NAME  = 'HTTP_X_ND_APP_NAME';
    const HEADER_UUID      = 'HTTP_X_ND_UUID';
    const X_ND_PREFIX      = 'X_ND';
    const EXCLUDED        = 'EXCLUDED';

    /**
     * @var int
     */
    private $id;

    /**
     * @var float
     */
    private $startTime;

    /** @var Exclude */
    private $exclude;

    /** @var Version|null */
    private $version;
    /**
     * @var string
     */
    private $logLevel;

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

    public function setExcluded(Exclude $exclude)
    {
        $this->exclude = $exclude;
    }


    /**
     * @param Version|null $version
     * @return $this
     */
    public function setAppVersionNumber(Version $version = null)
    {
        $this->version = $version;
        return $this;
    }

    /**
     * @param string $logLevel
     * @return $this
     */
    public function setLogLevel($logLevel)
    {
        $this->logLevel = $logLevel;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAppVersionNumber()
    {
        return $this->version instanceof Version ? $this->version->getVersionNumber() : null;
    }

    /**
     * @return string
     */
    public function getLogLevel()
    {
        return $this->logLevel;
    }

    public function filterExcludedFields(array $data)
    {
        $exclude = $this->exclude->getExclude();
        foreach ($exclude as $key) {
            if (isset($data[$key])) {
                $data[$key] = self::EXCLUDED;
            }
        }

        return  $data;
    }

    /**
     * @return int|null
     */
    protected function getDbProfilerRequestParam()
    {
        return array_key_exists(Override::DB_PROFILER, $_GET)
            ? (int) $_GET[Override::DB_PROFILER]
            : null;
    }

    /**
     * @return array
     */
    protected function getCpuLoad()
    {
        $load = sys_getloadavg();
        return [
            'cpu_load_1' => $load[0],
            'cpu_load_5' => $load[1],
            'cpu_load_15' => $load[2],
            'cpu_process' => $this->getUsageByPid(getmypid()),
        ];
    }

    /**
     * @return string
     */
    protected function getUsageByPid($pid)
    {
        $output = shell_exec("ps -p $pid -o %cpu");
        return trim(str_replace("%CPU", "", $output));
    }
}
