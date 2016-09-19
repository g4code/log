<?php

namespace G4\Log;

interface AdapterInterface
{

    public function save(array $data);

    public function saveAppend(array $data);

    public function saveInOneCall();
}