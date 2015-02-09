<?php

namespace G4\Log\Adapter;

abstract class AdapterAbstract
{

    /**
     * @var \G4\DataMapper\Domain\DomainAbstract
     */
    private $domain;


    public function getDomain()
    {
        return $this->domain;
    }

    public function setDomain(\G4\DataMapper\Domain\DomainAbstract $domain)
    {
        $this->domain = $domain;
        return $this;
    }

}