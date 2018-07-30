<?php

namespace G4\Log\Data;

class TaskerEnd extends LoggerAbstract
{

    /**
     * @var string
     */
    private $type;

    /**
     * @return array
     */
    public function getRawData()
    {
        return [
            'id'           => $this->getId(),
            'type'         => $this->type,
            'elapsed_time' => $this->getElapsedTime(),
        ];
    }

    /**
     * @param $type string
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }
}
