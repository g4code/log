<?php

namespace G4\Log\Buffer;

use G4\ValueObject\StringLiteral;

/**
 * The settings for the truncatedFields are set in the config file.
 * The example config is:
 *
 * truncated_fields.enabled = 1
 * truncated_fields.truncate_above = 50000 ; truncate fields longer than characters
 * truncated_fields.truncate_to = 5000 ; truncate fields to this many
 * ; which fields to truncate in which logs
 * truncated_fields.nd_requests[] = resource
 * truncated_fields.nd_requests[] = params
 */

class FieldsTruncator
{
    private const TRUNCATE_ENABLED = 'enabled';
    private const TRUNCATE_ABOVE = 'truncate_above';
    private const TRUNCATE_TO = 'truncate_to';
    private const TRUNCATE_SUFFIX = '...[truncated, change the truncated_fields.enable=1]';

    /**
     * @var string
     */
    private $logType;

    /**
     * @var array
     */
    private $config;

    public function __construct(StringLiteral $logType, array $configTruncatedFields)
    {
        $this->logType = (string) $logType;
        $this->config = $configTruncatedFields;
    }

    public function truncate(array $logData)
    {
        if (!$this->enabled()) {
            return $logData;
        }
        foreach ($logData as $key => $value) {
            $logData[$key] = $this->truncateValue($key, $value);
        }
        return $logData;
    }

    public function enabled(): bool
    {
        if (!isset($this->config[self::TRUNCATE_ENABLED])) {
            return true;
        }
        return (bool) $this->config[self::TRUNCATE_ENABLED];
    }

    /**
     * @param string $fieldName
     * @param mixed $value
     * @return mixed
     */
    private function truncateValue(string $fieldName, $value)
    {
        if (!$this->shouldTruncateField($fieldName) || !$value) {
            return $value;
        }
        if (strlen($value) < $this->config[self::TRUNCATE_ABOVE]) {
            return $value;
        }
        return substr($value, 0, (int) $this->config[self::TRUNCATE_TO]) . self::TRUNCATE_SUFFIX;
    }

    public function shouldTruncateField(string $fieldName): bool
    {
        if (!isset($this->config[$this->logType])) {
            return false;
        }
        return in_array($fieldName, $this->config[$this->logType], true);
    }
}
