<?php

namespace G4\Log\Data;

use G4\Runner\Profiler;

class Response extends RequestResponseAbstarct
{

    /**
     * @var Profiler
     */
    private $profiler;

    /**
     * @return array
     */
    public function getRawData()
    {
        $resource   = $this->getApplication()->getResponse()->getResponseObject();
        $appMessage = $this->getApplication()->getResponse()->getResponseMessage();
        $httpCode   = $this->getApplication()->getResponse()->getHttpResponseCode();

        return [
            'id'           => $this->getId(),
            'code'         => $httpCode,
            'message'      => $this->getApplication()->getResponse()->getHttpMessage(),
            'resource'     => empty($resource) ? null : \json_encode($resource),
            'app_code'     => $this->getApplication()->getResponse()->getApplicationResponseCode(),
            'app_message'  => empty($appMessage) ? null : \json_encode($appMessage),
            'elapsed_time' => $this->getElapsedTime(),
            'profiler'     => \json_encode($this->profiler->getProfilerOutput($httpCode)),
        ];
    }

    /**
     * @param Profiler $profiler
     * @return $this
     */
    public function setProfiler(Profiler $profiler)
    {
        $this->profiler = $profiler;
        return $this;
    }
}
