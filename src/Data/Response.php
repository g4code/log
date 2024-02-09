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

        $profilerOutput = $this->profiler
            ? $this->profiler->getProfilerOutput($httpCode, $this->getDbProfilerRequestParam())
            : [];

        $responseElapsedTimeInMs = (int) ($this->getElapsedTime() * 1000);

        $rawData = [
            'id'           => $this->getId(),
            'code'         => $httpCode,
            'message'      => $this->getApplication()->getResponse()->getHttpMessage(),
            'resource'     => empty($resource) ? null : \json_encode($resource),
            'app_code'     => $this->getApplication()->getResponse()->getApplicationResponseCode(),
            'app_message'  => empty($appMessage) ? null : \json_encode($appMessage),
            'elapsed_time' => $this->getElapsedTime(),
            'elapsed_time_ms' => $responseElapsedTimeInMs,
            'profiler'     => \json_encode($profilerOutput),
            'app_version'  => $this->getAppVersionNumber(),
        ];

        if($this->profiler->shouldLogProfilerOutput($responseElapsedTimeInMs)){
            $rawData['profiler'] =  \json_encode($this->profiler->getProfilerOutput($httpCode));
        }

        $rawData += $this->getCpuLoad();

        if ($this->profiler) {
            $rawData += $this->profiler->getProfilerSummary();
        }

        return $this->filterExcludedFields($rawData);
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
