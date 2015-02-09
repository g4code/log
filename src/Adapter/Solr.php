<?php

namespace G4\Log\Adapter;

class Solr extends AdapterAbstract
{

    private $params;


    public function __construct($params)
    {
        $this->params = $params;
    }

    public function save()
    {
        $this->getMapper()->update($this->getDomain());
    }

    public function saveAppend()
    {
        $this->getMapper()->updateAdd($this->getDomain());
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