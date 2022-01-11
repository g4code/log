<?php /** @noinspection PhpLanguageLevelInspection */

namespace G4\Log\Data;


class TaskerExecution extends LoggerAbstract
{
    const LOG_TYPE = 'execution';
    const CONTENT_LIMIT = 512;

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

    public function getRawData()
    {
        return [
            'id'        => $this->getId(),
            'timestamp' => $this->getJsTimestamp(),
            'hostname'  => \gethostname(),
            'pid'       => \getmypid(),
            'type'      => $this->logType?: self::LOG_TYPE,
            'memory_peak_usage'  => memory_get_peak_usage(),
            'exception' => $this->exception === null ?: \json_encode([
                    'message' => $this->exception->getMessage(),
                    'line'    => $this->exception->getLine(),
                    'code'    => $this->exception->getCode(),
                    'trace'   => $this->exception->getTrace(),
                ]
            ),

            'task_id'       => $this->task->getTaskId(),
            'recu_id'       => $this->task->getRecurringId(),
            'identifier'    => $this->task->getIdentifier(),
            'task'          => $this->task->getTask(),
            'data'          => $this->task->getData(),
            'output'        => $this->getOutput(),
            'request_uuid'  => $this->task->getRequestUuid(),
            'priority'      => $this->task->getPriority(),
            'status'        => $this->task->getStatus(),
            'ts_created'    => $this->task->getTsCreated(),
            'ts_started'    => $this->task->getTsStarted(),
            'exec_time'     => $this->task->getExecTime(),
            'exec_time_ms'  => (int) ($this->task->getExecTime() * 1000),
            'started_count' => $this->task->getStartedCount(),
            'php_version'   => str_replace(PHP_EXTRA_VERSION, '', PHP_VERSION),
        ];
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
