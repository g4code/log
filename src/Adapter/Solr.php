<?php

namespace G4\Log\Adapter;

use G4\Log\AdapterInterface;

class Solr implements AdapterInterface
{

    /**
     * @var array
     */
    private $params;

    /**
     * Solr constructor.
     * @param array $params
     */
    public function __construct(array $params)
    {
        $this->params = $params;
    }

    public function save($data)
    {
        $this->getMapper()->update($data);
    }

    public function saveAppend($data)
    {
        $this->getMapper()->updateAdd($data);
    }

    private function getMapper()
    {
        return new \G4\DataMapper\Mapper\Solr($this->getMapperAdapter());
    }

    private function getMapperAdapter()
    {
        return new \G4\DataMapper\Adapter\Solr\Curl($this->params);
    }
}