<?php

declare(strict_types=1);

namespace App\Tests\Service\DateTime;

use App\Service\DateTime\DateTimeParser;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class DateTimeParserTest extends TestCase
{
    private DateTimeParser $parser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->parser = new DateTimeParser();
    }

    public function testParse(): void
    {
        $result = $this->parser->parse('Y-m-d H:i:s', 'Europe/Moscow', '2007-01-02 01:02:03');
        self::assertSame('2007-01-01 22:02:03', $result->format('Y-m-d H:i:s'));

        $result = $this->parser->parse('Y-m-d H:i:s', 'Europe/Moscow', '2007-01-02 01:02');
        self::assertSame('2007-01-01 22:02:00', $result->format('Y-m-d H:i:s'));

        $result = $this->parser->parse('Y-m-d H:i:s', 'Europe/Moscow', '2007-01-02');
        self::assertSame('2007-01-01 21:00:00', $result->format('Y-m-d H:i:s'));

        $result = $this->parser->parse('Y-m-d H:i:s', 'Europe/Moscow', '2007');
        self::assertSame('2006-12-31 21:00:00', $result->format('Y-m-d H:i:s'));
    }

    public function testParseInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Failed to parse "this monday" date by "Y-m-d H:i:s" format');
        $this->parser->parse('Y-m-d H:i:s', 'Europe/Moscow', 'this monday');
    }
}
