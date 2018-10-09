<?php /** @noinspection PhpLanguageLevelInspection */

namespace G4\Log\Data;


class TaskerExecution extends LoggerAbstract
{
    const LOG_TYPE = 'execution';

    /**
     * @var \G4\Tasker\Model\Domain\Task
     */
    private $task;

    /**
     * @var \Exception
     */
    private $exception;

    /**
     * @var string
     */
    private $logType;

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
            'request_uuid'  => $this->task->getRequestUuid(),
            'priority'      => $this->task->getPriority(),
            'status'        => $this->task->getStatus(),
            'ts_created'    => $this->task->getTsCreated(),
            'ts_started'    => $this->task->getTsStarted(),
            'exec_time'     => $this->task->getExecTime(),
            'started_count' => $this->task->getStartedCount(),
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
     * @param \Exception $exception
     * @return $this
     */
    public function setException(\Exception $exception)
    {
        $this->exception = $exception;
        return $this;
    }

    public function setLogType($logType)
    {
        $this->logType = $logType;
        return $this;
    }
}