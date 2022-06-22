<?php

namespace G4\Log\Data;

class Exclude
{
    /** @var string */
    private $module;

    /** @var string */
    private $service;

    /** @var array */
    private $exclude;

    /**
     * @param array $exclude
     */
    public function __construct($exclude = [])
    {
        $this->exclude = $exclude;
    }

    /**
     * @param string $module
     */
    public function setModule($module)
    {
        $this->module = strtolower($module);
    }

    /**
     * @param string $service
     */
    public function setService($service)
    {
        $this->service = strtolower($service);
    }

    /**
     * @return array
     */
    public function getExclude()
    {
        if(empty($this->exclude)) {
            return [];
        }

        $method = strtolower(isset($_SERVER['REQUEST_METHOD'])  ? $_SERVER['REQUEST_METHOD']  : null);

        if($this->isModuleSet() && $this->isServiceSet()) {
            $exclude = $this->exclude[$this->module][$this->service];

            return empty($method) ? $exclude : (isset($exclude[$method]) ? $exclude[$method] : []);
        }

        return [];
    }

    /**
     * @return bool
     */
    private function isModuleSet()
    {
        return isset($this->exclude[$this->module]);
    }

    /**
     * @return bool
     */
    private function isServiceSet()
    {
        return isset($this->exclude[$this->module][$this->service]);
    }
}