<?php /** @noinspection PhpLanguageLevelInspection */

namespace G4\Log\Data;


use G4\Runner\Profiler;

class TaskerExecution extends LoggerAbstract
{
    const LOG_TYPE = 'execution';
    const CONTENT_LIMIT = 30000;

    /**
     * @var \G4\Tasker\Model\Domain\Task
     */
    private $task;

    /**
     * @var \Exception|\Throwable
     */
    private $exception;

    /**
     * @var string
     */
    private $logType;

    /**
     * @var string
     */
    private $output;

    /**
     * @var Profiler
     */
    private $profiler;

    private $taskFinished = false;

    public function taskStarted()
    {
        $this->taskFinished = false;
    }

    public function taskFinished()
    {
        $this->taskFinished = true;
    }

    public function getRawData()
    {
        $rawData = $this->taskFinished
            ? $this->getRawDataEnd()
            : $this->getRawDataStart();
        return $rawData;
    }

    private function getRawDataStart()
    {
        return [
            'id' => $this->getId(),
            'timestamp' => $this->getJsTimestamp(),
            'hostname' => \gethostname(),
            'pid' => \getmypid(),
            'type' => $this->logType ?: self::LOG_TYPE,
            'task_id' => $this->task->getTaskId(),
            'recu_id' => $this->task->getRecurringId(),
            'identifier' => $this->task->getIdentifier(),
            'task' => $this->task->getTask(),
            'data' => $this->task->getData(),
            'request_uuid' => $this->task->getRequestUuid(),
            'priority' => $this->task->getPriority(),
            'status' => $this->task->getStatus(),
            'ts_created' => $this->task->getTsCreated(),
            'ts_started' => $this->task->getTsStarted(),
            'started_count' => $this->task->getStartedCount(),
            'php_version' => str_replace(PHP_EXTRA_VERSION, '', PHP_VERSION),
            'app_version' => $this->getAppVersionNumber(),
            'queue_source' => method_exists($this->task, 'getQueueSource')
                ? $this->task->getQueueSource() : null,
        ];
    }

    private function getRawDataEnd()
    {
        $profilerOutput = $this->profiler
            ? $this->profiler->getTaskerProfilerOutput($this->task->getStatus(), $this->task->getExecTime())
            : null;

        $rawData = [
            'id' => $this->getId(),
            'ts_finished' => $this->task->getTsFinished(),
            'memory_peak_usage' => memory_get_peak_usage(),
            'exception' => $this->exception === null
                ? null
                : \json_encode([
                        'message' => $this->exception->getMessage(),
                        'line' => $this->exception->getLine(),
                        'code' => $this->exception->getCode(),
                        'trace' => $this->exception->getTrace(),
                    ]
                ),
            'output' => $this->getOutput(),
            'exec_time' => $this->task->getExecTime(),
            'exec_time_ms' => (int)($this->task->getExecTime() * 1000),
            'status' => $this->task->getStatus(),
            'started_count' => $this->task->getStartedCount(),
            'profiler' => $profilerOutput ? \json_encode($profilerOutput) : null,
        ];

        $rawData += $this->getCpuLoad();

        if ($this->profiler) {
            $rawData += $this->profiler->getProfilerSummary();
        }

        return $rawData;
    }

    /**
     * @param $task \G4\Tasker\Model\Domain\Task
     * @return $this
     */
    public function setTask(\G4\Tasker\Model\Domain\Task $task)
    {
        $this->task = $task;
        return $this;
    }

    /**
     * @param \Exception|\Throwable $exception
     * @return $this
     */
    public function setException($exception)
    {
        $this->exception = $exception;
        return $this;
    }

    public function setLogType($logType)
    {
        $this->logType = $logType;
        return $this;
    }

    public function setProfiler(Profiler $profiler)
    {
        $this->profiler = $profiler;
        return $this;
    }

    public function hasProfiler()
    {
        return $this->profiler !== null;
    }

    /**
     * @param string
     */
    public function setOutput($output)
    {
        $this->output = $output
            ? substr($output, 0, self::CONTENT_LIMIT)
            : null;
    }

    /**
     * @return string
     */
    public function getOutput()
    {
        return $this->output;
    }
}
