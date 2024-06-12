<?php

namespace Buffer;

use G4\Log\Buffer\FieldsTruncator;
use G4\ValueObject\StringLiteral;

class FieldsTruncatorTest extends \PHPUnit\Framework\TestCase
{
    /** @var array[] */
    private $config;
    protected function setUp(): void
    {
        $this->config = [
            'enabled' => 1,
            'truncate_above' => 100,
            'truncate_to' => 50,
            'nd_requests' => [
                'resource',
                'params',
            ],
        ];
    }

    public function testEnabled(): void
    {
        $config = $this->config;
        $fieldsTruncator = new FieldsTruncator(new StringLiteral('nd_requests'), $config);
        self::assertTrue($fieldsTruncator->enabled());
        $config = $this->config;
        $config['enabled'] = 0;
        $fieldsTruncator = new FieldsTruncator(new StringLiteral('nd_requests'), $config);
        self::assertFalse($fieldsTruncator->enabled());
    }

    public function testShouldTruncateField(): void
    {
        $fieldsTruncator = new FieldsTruncator(new StringLiteral('nd_requests'), $this->config);
        self::assertTrue($fieldsTruncator->shouldTruncateField('resource'));
        self::assertTrue($fieldsTruncator->shouldTruncateField('params'));
        self::assertFalse($fieldsTruncator->shouldTruncateField('app_message'));
    }

    public function testTruncate(): void
    {
        $fieldsTruncator = new FieldsTruncator(new StringLiteral('nd_requests'), $this->config);
        $logData = [
            'app_message' => str_repeat('x', 200),
            'resource' => str_repeat('y', 200),
            'params' => str_repeat('z', 200),
        ];

        $suffix = '...[truncated, change the truncated_fields.enable=1]';
        $expected = [
            'app_message' => str_repeat('x', 200),
            'resource' => str_repeat('y', 50) . $suffix,
            'params' => str_repeat('z', 50) . $suffix,
        ];

        self::assertSame($expected, $fieldsTruncator->truncate($logData));
    }
}
