<?php

namespace G4\Log\Buffer;

use G4\ValueObject\StringLiteral;

class PrettifyJson
{
    /**
     * @var StringLiteral
     */
    private $logType;

    /**
     * @var array
     */
    private $prettyPrintFields;

    public function __construct(StringLiteral $logType, array $prettyPrintFields)
    {
        $this->logType = $logType;
        $this->prettyPrintFields = $prettyPrintFields;
    }

    public function prettify(array $logData)
    {
        $logType = (string) $this->logType;
        $fields = isset($this->prettyPrintFields[$logType]) ? $this->prettyPrintFields[$logType] : [];
        if (!count($fields)) {
            return $logData;
        }
        foreach ($fields as $field) {
            if (isset($logData[$field])) {
                try {
                    $decodedJson = json_decode($logData[$field], true); // php 5.6 compatible
                    if (!$decodedJson) {
                        throw new \Exception('Invalid JSON');
                    }
                    $logData[$field] = json_encode($decodedJson, JSON_PRETTY_PRINT);
                } catch (\Exception $exception) {
                    // if exception decoding json, do nothing
                }
            }
        }
        return $logData;
    }
}