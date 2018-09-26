<?php

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

    public function getRawData()
    {
        return [
            'id'        => $this->getId(),
            'timestamp' => $this->getJsTimestamp(),
            'datetime'  => \date('Y-m-d H:i:s'),
            'hostname'  => \gethostname(),
            'pid'       => \getmypid(),
            'type'      => self::LOG_TYPE,
            'memory_peak_usage'  => memory_get_peak_usage(true),
            'exception'   => $this->exception !== null ? \json_encode($this->exception) : null,

            'task_id'            => $this->task->getTaskId(),
            'task_recu_id'       => $this->task->getRecurringId(),
            'task_identifier'    => $this->task->getIdentifier(),
            'task_task'          => $this->task->getTask(),
            'task_data'          => $this->task->getData(),
            'task_request_uuid'  => $this->task->getRequestUuid(),
            'task_priority'      => $this->task->getPriority(),
            'task_status'        => $this->task->getStatus(),
            'task_ts_created'    => $this->task->getTsCreated(),
            'task_ts_started'    => $this->task->getTsStarted(),
            'task_exec_time'     => $this->task->getExecTime(),
            'task_started_count' => $this->task->getStartedCount(),
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
}