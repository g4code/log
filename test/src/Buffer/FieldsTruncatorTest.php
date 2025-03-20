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
            'nd_test' => [
                'app_message',
                'code',
                'params',
            ],
            'nd_requests' => [
                'resource',
                'params',
                'profiler',
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
        self::assertTrue($fieldsTruncator->shouldTruncateField('profiler'));
        self::assertFalse($fieldsTruncator->shouldTruncateField('app_message'));
    }

    /**
     * @dataProvider truncateDataProvider
     */
    public function testTruncateField(string $indexName, string $fieldName, bool $expectedResult): void
    {
        $truncator = new FieldsTruncator(new StringLiteral($indexName), $this->config);
        self::assertSame($expectedResult, $truncator->shouldTruncateField($fieldName));
    }

    public function testTruncate(): void
    {
        $fieldsTruncator = new FieldsTruncator(new StringLiteral('nd_requests'), $this->config);
        $logData = [
            'app_message' => str_repeat('x', 200),
            'resource' => str_repeat('y', 200),
            'params' => str_repeat('z', 200),
            'code' => 201,
            'elapsed_time' => 1.05,
        ];

        $suffix = '...[truncated, change the truncated_fields.enable=1]';
        $expected = [
            'app_message' => str_repeat('x', 200),
            'resource' => str_repeat('y', 50) . $suffix,
            'params' => str_repeat('z', 50) . $suffix,
            'code' => 201,
            'elapsed_time' => 1.05,
        ];

        self::assertSame($expected, $fieldsTruncator->truncate($logData));
    }

    public function truncateDataProvider(): array
    {
        return [
            ['nd_requests', 'params', true],
            ['nd_requests', 'resource', true],
            ['nd_requests', 'profiler', true],
            ['nd_requests', 'app_message', false],
            ['nd_requests', 'app_version', false],
            ['nd_requests', 'method', false],
            ['mailer_sent_emails', 'params', false],
            ['mailer_sent_emails', 'resource', false],
            ['mailer_sent_emails', 'profiler', false],
            ['mailer_sent_emails', 'app_message', false],
            ['mailer_sent_emails', 'app_version', false],
            ['mailer_sent_emails', 'method', false],
            ['nd_test', 'profiler', false],
            ['nd_test', 'resource', false],
            ['nd_test', 'app_message', true],
            ['nd_test', 'code', true],
            ['nd_test', 'params', true],
        ];
    }
}
