<?php

namespace G4\Log;

interface AdapterInterface
{

    public function save($data);

    public function saveAppend($data);
}