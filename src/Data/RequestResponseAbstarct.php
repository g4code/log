<?php

namespace G4\Log\Data;

use G4\CleanCore\Application;

abstract class RequestResponseAbstarct extends LoggerAbstract
{
    /**
     * @var Application
     */
    private $application;

    /**
     * @return Application
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * @param Application $application
     * @return $this
     */
    public function setApplication(Application $application)
    {
        $this->application = $application;
        return $this;
    }

    /**
     * @param Exclude $exclude
     * @return void
     */
    public function setExcluded(Exclude $exclude)
    {
        $exclude->setModule($this->getApplication()->getAppNamespace());
        $exclude->setService($this->getApplication()->getRequest()->getResourceName());

        parent::setExcluded($exclude);
    }
}