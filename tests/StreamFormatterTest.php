<?php
/**
 * This file is part of the mimmi20/monolog-streamformatter package.
 *
 * Copyright (c) 2022-2023, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Mimmi20Test\Monolog\Formatter;

use DateTimeImmutable;
use Mimmi20\Monolog\Formatter\StreamFormatter;
use Monolog\Formatter\LineFormatter;
use Monolog\Formatter\NormalizerFormatter;
use Monolog\Level;
use Monolog\LogRecord;
use OutOfRangeException;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use ReflectionProperty;
use RuntimeException;
use SebastianBergmann\RecursionContext\InvalidArgumentException;
use stdClass;
use UnexpectedValueException;

use function count;
use function explode;
use function str_pad;
use function str_replace;

use const PHP_EOL;

final class StreamFormatterTest extends TestCase
{
    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function testConstructWithDefaults(): void
    {
        $formatter = new StreamFormatter();

        self::assertSame(NormalizerFormatter::SIMPLE_DATE, $formatter->getDateFormat());
        self::assertSame(9, $formatter->getMaxNormalizeDepth());
        self::assertSame(1000, $formatter->getMaxNormalizeItemCount());

        $ailb = new ReflectionProperty($formatter, 'allowInlineLineBreaks');

        self::assertFalse($ailb->getValue($formatter));

        $format = new ReflectionProperty($formatter, 'format');

        self::assertSame(StreamFormatter::SIMPLE_FORMAT, $format->getValue($formatter));

        $st = new ReflectionProperty($formatter, 'includeStacktraces');

        self::assertFalse($st->getValue($formatter));

        $ts = new ReflectionProperty($formatter, 'tableStyle');

        self::assertSame(StreamFormatter::BOX_STYLE, $ts->getValue($formatter));
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function testConstructWithValues(): void
    {
        $format     = '[%level_name%] %message%';
        $tableStyle = 'test-style';
        $dateFormat = 'c';

        $formatter = new StreamFormatter($format, $tableStyle, $dateFormat, true, false);

        self::assertSame($dateFormat, $formatter->getDateFormat());
        self::assertSame(9, $formatter->getMaxNormalizeDepth());
        self::assertSame(1000, $formatter->getMaxNormalizeItemCount());

        $ailb = new ReflectionProperty($formatter, 'allowInlineLineBreaks');

        self::assertTrue($ailb->getValue($formatter));

        $formatP = new ReflectionProperty($formatter, 'format');

        self::assertSame($format, $formatP->getValue($formatter));

        $st = new ReflectionProperty($formatter, 'includeStacktraces');

        self::assertFalse($st->getValue($formatter));

        $ts = new ReflectionProperty($formatter, 'tableStyle');

        self::assertSame($tableStyle, $ts->getValue($formatter));
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function testConstructWithValues2(): void
    {
        $format     = '[%level_name%] %message%';
        $tableStyle = 'test-style';
        $dateFormat = 'c';

        $formatter = new StreamFormatter($format, $tableStyle, $dateFormat, false, true);

        self::assertSame($dateFormat, $formatter->getDateFormat());
        self::assertSame(9, $formatter->getMaxNormalizeDepth());
        self::assertSame(1000, $formatter->getMaxNormalizeItemCount());

        $ailb = new ReflectionProperty($formatter, 'allowInlineLineBreaks');

        self::assertTrue($ailb->getValue($formatter));

        $formatP = new ReflectionProperty($formatter, 'format');

        self::assertSame($format, $formatP->getValue($formatter));

        $st = new ReflectionProperty($formatter, 'includeStacktraces');

        self::assertTrue($st->getValue($formatter));

        $ts = new ReflectionProperty($formatter, 'tableStyle');

        self::assertSame($tableStyle, $ts->getValue($formatter));
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function testConstructWithValues3(): void
    {
        $format     = '[%level_name%] %message%';
        $tableStyle = 'test-style';
        $dateFormat = 'c';

        $formatter = new StreamFormatter($format, $tableStyle, $dateFormat, false, false);

        self::assertSame($dateFormat, $formatter->getDateFormat());
        self::assertSame(9, $formatter->getMaxNormalizeDepth());
        self::assertSame(1000, $formatter->getMaxNormalizeItemCount());

        $ailb = new ReflectionProperty($formatter, 'allowInlineLineBreaks');

        self::assertFalse($ailb->getValue($formatter));

        $formatter->allowInlineLineBreaks();

        $ailb = new ReflectionProperty($formatter, 'allowInlineLineBreaks');

        self::assertTrue($ailb->getValue($formatter));

        $formatP = new ReflectionProperty($formatter, 'format');

        self::assertSame($format, $formatP->getValue($formatter));

        $st = new ReflectionProperty($formatter, 'includeStacktraces');

        self::assertFalse($st->getValue($formatter));

        $ts = new ReflectionProperty($formatter, 'tableStyle');

        self::assertSame($tableStyle, $ts->getValue($formatter));
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function testConstructWithValues4(): void
    {
        $format     = '[%level_name%] %message%';
        $tableStyle = 'test-style';
        $dateFormat = 'c';

        $formatter = new StreamFormatter($format, $tableStyle, $dateFormat, true, false);

        self::assertSame($dateFormat, $formatter->getDateFormat());
        self::assertSame(9, $formatter->getMaxNormalizeDepth());
        self::assertSame(1000, $formatter->getMaxNormalizeItemCount());

        $ailb = new ReflectionProperty($formatter, 'allowInlineLineBreaks');

        self::assertTrue($ailb->getValue($formatter));

        $formatter->allowInlineLineBreaks(false);

        $ailb = new ReflectionProperty($formatter, 'allowInlineLineBreaks');

        self::assertFalse($ailb->getValue($formatter));

        $formatP = new ReflectionProperty($formatter, 'format');

        self::assertSame($format, $formatP->getValue($formatter));

        $st = new ReflectionProperty($formatter, 'includeStacktraces');

        self::assertFalse($st->getValue($formatter));

        $formatter->includeStacktraces();

        $ailb = new ReflectionProperty($formatter, 'allowInlineLineBreaks');

        self::assertTrue($ailb->getValue($formatter));

        $st = new ReflectionProperty($formatter, 'includeStacktraces');

        self::assertTrue($st->getValue($formatter));

        $ts = new ReflectionProperty($formatter, 'tableStyle');

        self::assertSame($tableStyle, $ts->getValue($formatter));
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function testFormat(): void
    {
        $message  = 'test message';
        $channel  = 'test-channel';
        $datetime = new DateTimeImmutable('now');

        $formatter = new StreamFormatter();

        $record = new LogRecord(
            datetime: $datetime,
            channel: $channel,
            level: Level::Error,
            message: $message,
            context: [],
            extra: [],
        );

        $formatted = $formatter->format($record);

        $expected = '============================================================================================================================================================================================================================

test message


┌──────────────────────┬──────────────────────┬──────────────────────────────────────────────────────────────────────────────────── ERROR ───────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┐
│ General Info                                                                                                                                                                                                                                                               │
├──────────────────────┼──────────────────────┼──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│                 Time │ ' . $datetime->format(NormalizerFormatter::SIMPLE_DATE) . '                                                                                                                                                                                                                           │
│                Level │ ERROR                                                                                                                                                                                                                                               │
└──────────────────────┴──────────────────────┴──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┘

';

        self::assertSame(str_replace(["\r\n", "\n", "\r"], PHP_EOL, $expected), $formatted);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function testFormat2(): void
    {
        $message  = 'test message';
        $channel  = 'test-channel';
        $datetime = new DateTimeImmutable('now');

        $formatter = new StreamFormatter();

        $record = new LogRecord(
            datetime: $datetime,
            channel: $channel,
            level: Level::Error,
            message: $message,
            context: ['one' => null, 'two' => true, 'three' => false, 'four' => ['abc', 'xyz']],
            extra: ['app' => 'test-app'],
        );

        $formatted = $formatter->format($record);

        $expected = '============================================================================================================================================================================================================================

test message


┌──────────────────────┬──────────────────────┬──────────────────────────────────────────────────────────────────────────────────── ERROR ───────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┐
│ General Info                                                                                                                                                                                                                                                               │
├──────────────────────┼──────────────────────┼──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│                 Time │ ' . $datetime->format(NormalizerFormatter::SIMPLE_DATE) . '                                                                                                                                                                                                                           │
│                Level │ ERROR                                                                                                                                                                                                                                               │
├──────────────────────┼──────────────────────┼──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ Extra                                                                                                                                                                                                                                                                      │
├──────────────────────┼──────────────────────┼──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│                  app │ test-app                                                                                                                                                                                                                                            │
├──────────────────────┼──────────────────────┼──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ Context                                                                                                                                                                                                                                                                    │
├──────────────────────┼──────────────────────┼──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│                  one │ NULL                                                                                                                                                                                                                                                │
│                  two │ true                                                                                                                                                                                                                                                │
│                three │ false                                                                                                                                                                                                                                               │
│                 four │ 0                    │ abc                                                                                                                                                                                                                          │
│                      │ 1                    │ xyz                                                                                                                                                                                                                          │
└──────────────────────┴──────────────────────┴──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┘

';

        self::assertSame($expected, $formatted);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function testFormat3(): void
    {
        $message  = 'test message';
        $channel  = 'test-channel';
        $datetime = new DateTimeImmutable('now');

        $formatter = new StreamFormatter('%message% %context.two% %extra.app%');

        $record = new LogRecord(
            datetime: $datetime,
            channel: $channel,
            level: Level::Error,
            message: $message,
            context: ['one' => null, 'two' => true, 'three' => false, 'four' => ['abc', 'xyz']],
            extra: ['app' => 'test-app'],
        );

        $formatted = $formatter->format($record);

        $expected = '============================================================================================================================================================================================================================

test message true test-app


┌──────────────────────┬──────────────────────┬──────────────────────────────────────────────────────────────────────────────────── ERROR ───────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┐
│ General Info                                                                                                                                                                                                                                                               │
├──────────────────────┼──────────────────────┼──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│                 Time │ ' . $datetime->format(NormalizerFormatter::SIMPLE_DATE) . '                                                                                                                                                                                                                           │
│                Level │ ERROR                                                                                                                                                                                                                                               │
├──────────────────────┼──────────────────────┼──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ Extra                                                                                                                                                                                                                                                                      │
├──────────────────────┼──────────────────────┼──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│                  app │ test-app                                                                                                                                                                                                                                            │
├──────────────────────┼──────────────────────┼──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ Context                                                                                                                                                                                                                                                                    │
├──────────────────────┼──────────────────────┼──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│                  one │ NULL                                                                                                                                                                                                                                                │
│                  two │ true                                                                                                                                                                                                                                                │
│                three │ false                                                                                                                                                                                                                                               │
│                 four │ 0                    │ abc                                                                                                                                                                                                                          │
│                      │ 1                    │ xyz                                                                                                                                                                                                                          │
└──────────────────────┴──────────────────────┴──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┘

';

        self::assertSame($expected, $formatted);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function testFormat4(): void
    {
        $message  = 'test message';
        $channel  = 'test-channel';
        $datetime = new DateTimeImmutable('now');

        $formatter = new StreamFormatter('%message% %context.four% %extra.app%');

        $record = new LogRecord(
            datetime: $datetime,
            channel: $channel,
            level: Level::Error,
            message: $message,
            context: ['one' => null, 'two' => true, 'three' => false, 'four' => ['abc', 'xyz']],
            extra: ['app' => 'test-app'],
        );

        $formatted = $formatter->format($record);

        $expected = '============================================================================================================================================================================================================================

test message ["abc","xyz"] test-app


┌──────────────────────┬──────────────────────┬──────────────────────────────────────────────────────────────────────────────────── ERROR ───────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┐
│ General Info                                                                                                                                                                                                                                                               │
├──────────────────────┼──────────────────────┼──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│                 Time │ ' . $datetime->format(NormalizerFormatter::SIMPLE_DATE) . '                                                                                                                                                                                                                           │
│                Level │ ERROR                                                                                                                                                                                                                                               │
├──────────────────────┼──────────────────────┼──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ Extra                                                                                                                                                                                                                                                                      │
├──────────────────────┼──────────────────────┼──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│                  app │ test-app                                                                                                                                                                                                                                            │
├──────────────────────┼──────────────────────┼──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ Context                                                                                                                                                                                                                                                                    │
├──────────────────────┼──────────────────────┼──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│                  one │ NULL                                                                                                                                                                                                                                                │
│                  two │ true                                                                                                                                                                                                                                                │
│                three │ false                                                                                                                                                                                                                                               │
│                 four │ 0                    │ abc                                                                                                                                                                                                                          │
│                      │ 1                    │ xyz                                                                                                                                                                                                                          │
└──────────────────────┴──────────────────────┴──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┘

';

        self::assertSame($expected, $formatted);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function testFormat5(): void
    {
        $message  = 'test message';
        $channel  = 'test-channel';
        $datetime = new DateTimeImmutable('now');

        $formatter = new StreamFormatter('%message% %context.five% %extra.app%', 'default', null, false);

        $record = new LogRecord(
            datetime: $datetime,
            channel: $channel,
            level: Level::Error,
            message: $message,
            context: ['one' => null, 'two' => true, 'three' => false, 'four' => ['abc', 'xyz'], 'five' => "test\ntest"],
            extra: ['app' => 'test-app'],
        );

        $formatted = $formatter->format($record);

        $expected = '============================================================================================================================================================================================================================

test message test test test-app


+----------------------+----------------------+------------------------------------------------------------------------------------ ERROR -----------------------------------------------------------------------------------------------------------------------------------+
| General Info                                                                                                                                                                                                                                                               |
+----------------------+----------------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
|                 Time | ' . $datetime->format(NormalizerFormatter::SIMPLE_DATE) . '                                                                                                                                                                                                                           |
|                Level | ERROR                                                                                                                                                                                                                                               |
+----------------------+----------------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| Extra                                                                                                                                                                                                                                                                      |
+----------------------+----------------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
|                  app | test-app                                                                                                                                                                                                                                            |
+----------------------+----------------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| Context                                                                                                                                                                                                                                                                    |
+----------------------+----------------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
|                  one | NULL                                                                                                                                                                                                                                                |
|                  two | true                                                                                                                                                                                                                                                |
|                three | false                                                                                                                                                                                                                                               |
|                 four | 0                    | abc                                                                                                                                                                                                                          |
|                      | 1                    | xyz                                                                                                                                                                                                                          |
|                 five | test                                                                                                                                                                                                                                                |
|                      | test                                                                                                                                                                                                                                                |
+----------------------+----------------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+

';

        self::assertSame($expected, $formatted);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function testFormat6(): void
    {
        $message  = 'test message';
        $channel  = 'test-channel';
        $datetime = new DateTimeImmutable('now');

        $formatter = new StreamFormatter('%message% %context.five% %extra.app%', 'default', null, true);

        $record = new LogRecord(
            datetime: $datetime,
            channel: $channel,
            level: Level::Error,
            message: $message,
            context: ['one' => null, 'two' => true, 'three' => false, 'four' => ['abc', 'xyz'], 'five' => "test\ntest"],
            extra: ['app' => 'test-app'],
        );

        $formatted = $formatter->format($record);

        $expected = '============================================================================================================================================================================================================================

test message test
test test-app


+----------------------+----------------------+------------------------------------------------------------------------------------ ERROR -----------------------------------------------------------------------------------------------------------------------------------+
| General Info                                                                                                                                                                                                                                                               |
+----------------------+----------------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
|                 Time | ' . $datetime->format(NormalizerFormatter::SIMPLE_DATE) . '                                                                                                                                                                                                                           |
|                Level | ERROR                                                                                                                                                                                                                                               |
+----------------------+----------------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| Extra                                                                                                                                                                                                                                                                      |
+----------------------+----------------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
|                  app | test-app                                                                                                                                                                                                                                            |
+----------------------+----------------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| Context                                                                                                                                                                                                                                                                    |
+----------------------+----------------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
|                  one | NULL                                                                                                                                                                                                                                                |
|                  two | true                                                                                                                                                                                                                                                |
|                three | false                                                                                                                                                                                                                                               |
|                 four | 0                    | abc                                                                                                                                                                                                                          |
|                      | 1                    | xyz                                                                                                                                                                                                                          |
|                 five | test                                                                                                                                                                                                                                                |
|                      | test                                                                                                                                                                                                                                                |
+----------------------+----------------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+

';

        self::assertSame($expected, $formatted);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function testFormat7(): void
    {
        $message   = 'test message';
        $channel   = 'test-channel';
        $datetime  = new DateTimeImmutable('now');
        $exception = new RuntimeException('error');
        $trace     = explode(PHP_EOL, $exception->getTraceAsString());

        $formatter = new StreamFormatter('%message% %context.five% <%extra.Exception%>', 'default', null, true);

        $record = new LogRecord(
            datetime: $datetime,
            channel: $channel,
            level: Level::Error,
            message: $message,
            context: ['one' => null, 'two' => true, 'three' => false, 'four' => ['abc', 'xyz'], 'five' => "test\ntest"],
            extra: ['app' => 'test-app', 'Exception' => $exception],
        );

        $formatted = $formatter->format($record);

        $expected = '============================================================================================================================================================================================================================

test message test
test <[object] (RuntimeException(code: ' . $exception->getCode() . '): error at ' . $exception->getFile() . ':' . $exception->getLine() . ')>


+----------------------+----------------------+------------------------------------------------------------------------------------ ERROR -----------------------------------------------------------------------------------------------------------------------------------+
| General Info                                                                                                                                                                                                                                                               |
+----------------------+----------------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
|                 Time | ' . $datetime->format(NormalizerFormatter::SIMPLE_DATE) . '                                                                                                                                                                                                                           |
|                Level | ERROR                                                                                                                                                                                                                                               |
+----------------------+----------------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| Extra                                                                                                                                                                                                                                                                      |
+----------------------+----------------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
|                  app | test-app                                                                                                                                                                                                                                            |
|            Exception | Type                 | RuntimeException                                                                                                                                                                                                             |
|                      | Message              | error                                                                                                                                                                                                                        |
|                      | Code                 | 0                                                                                                                                                                                                                            |
|                      | File                 | ' . str_pad($exception->getFile(), 220) . ' |
|                      | Line                 | ' . str_pad((string) $exception->getLine(), 220) . ' |
|                      | Trace                | ' . str_pad($trace[0], 220) . ' |
';
        for ($i = 1, $count = count($trace); $i < $count; ++$i) {
            $expected .= '|                      |                      | ' . str_pad($trace[$i], 220) . ' |
';
        }

        $expected .= '+----------------------+----------------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| Context                                                                                                                                                                                                                                                                    |
+----------------------+----------------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
|                  one | NULL                                                                                                                                                                                                                                                |
|                  two | true                                                                                                                                                                                                                                                |
|                three | false                                                                                                                                                                                                                                               |
|                 four | 0                    | abc                                                                                                                                                                                                                          |
|                      | 1                    | xyz                                                                                                                                                                                                                          |
|                 five | test                                                                                                                                                                                                                                                |
|                      | test                                                                                                                                                                                                                                                |
+----------------------+----------------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+

';

        self::assertSame($expected, $formatted);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function testFormat8(): void
    {
        $message  = 'test message';
        $channel  = 'test-channel';
        $datetime = new DateTimeImmutable('now');

        $exception = new RuntimeException('error');

        $trace = explode(PHP_EOL, $exception->getTraceAsString());

        $formatter = new StreamFormatter('%message% %context.five% %extra.app%', 'default', null, true);

        $record = new LogRecord(
            datetime: $datetime,
            channel: $channel,
            level: Level::Error,
            message: $message,
            context: ['one' => null, 'two' => true, 'three' => false, 'four' => ['abc', 'xyz'], 'five' => "test\ntest"],
            extra: ['app' => 'test-app', 'Exception' => $exception],
        );

        $formatted = $formatter->format($record);

        $expected = '============================================================================================================================================================================================================================

test message test
test test-app


+----------------------+----------------------+------------------------------------------------------------------------------------ ERROR -----------------------------------------------------------------------------------------------------------------------------------+
| General Info                                                                                                                                                                                                                                                               |
+----------------------+----------------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
|                 Time | ' . $datetime->format(NormalizerFormatter::SIMPLE_DATE) . '                                                                                                                                                                                                                           |
|                Level | ERROR                                                                                                                                                                                                                                               |
+----------------------+----------------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| Extra                                                                                                                                                                                                                                                                      |
+----------------------+----------------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
|                  app | test-app                                                                                                                                                                                                                                            |
|            Exception | Type                 | RuntimeException                                                                                                                                                                                                             |
|                      | Message              | error                                                                                                                                                                                                                        |
|                      | Code                 | 0                                                                                                                                                                                                                            |
|                      | File                 | ' . str_pad($exception->getFile(), 220) . ' |
|                      | Line                 | ' . str_pad((string) $exception->getLine(), 220) . ' |
|                      | Trace                | ' . str_pad($trace[0], 220) . ' |
';
        for ($i = 1, $count = count($trace); $i < $count; ++$i) {
            $expected .= '|                      |                      | ' . str_pad($trace[$i], 220) . ' |
';
        }

        $expected .= '+----------------------+----------------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| Context                                                                                                                                                                                                                                                                    |
+----------------------+----------------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
|                  one | NULL                                                                                                                                                                                                                                                |
|                  two | true                                                                                                                                                                                                                                                |
|                three | false                                                                                                                                                                                                                                               |
|                 four | 0                    | abc                                                                                                                                                                                                                          |
|                      | 1                    | xyz                                                                                                                                                                                                                          |
|                 five | test                                                                                                                                                                                                                                                |
|                      | test                                                                                                                                                                                                                                                |
+----------------------+----------------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+

';

        self::assertSame($expected, $formatted);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function testFormat9(): void
    {
        $message  = 'test message';
        $channel  = 'test-channel';
        $datetime = new DateTimeImmutable('now');

        $exception1 = new RuntimeException('error');
        $exception2 = new UnexpectedValueException('error', 4711, $exception1);
        $exception3 = new OutOfRangeException('error', 1234, $exception2);

        $trace1 = explode(PHP_EOL, $exception1->getTraceAsString());
        $trace2 = explode(PHP_EOL, $exception2->getTraceAsString());
        $trace3 = explode(PHP_EOL, $exception3->getTraceAsString());

        $formatter = new StreamFormatter('%message% %context.five% %extra.app%', 'default', null, true);

        $record = new LogRecord(
            datetime: $datetime,
            channel: $channel,
            level: Level::Error,
            message: $message,
            context: ['one' => null, 'two' => true, 'three' => false, 'four' => ['abc', 'xyz'], 'five' => "test\ntest"],
            extra: ['app' => 'test-app', 'Exception' => $exception3],
        );

        $formatted = $formatter->format($record);

        $expected = '============================================================================================================================================================================================================================

test message test
test test-app


+----------------------+----------------------+------------------------------------------------------------------------------------ ERROR -----------------------------------------------------------------------------------------------------------------------------------+
| General Info                                                                                                                                                                                                                                                               |
+----------------------+----------------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
|                 Time | ' . $datetime->format(NormalizerFormatter::SIMPLE_DATE) . '                                                                                                                                                                                                                           |
|                Level | ERROR                                                                                                                                                                                                                                               |
+----------------------+----------------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| Extra                                                                                                                                                                                                                                                                      |
+----------------------+----------------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
|                  app | test-app                                                                                                                                                                                                                                            |
|            Exception | Type                 | ' . str_pad($exception3::class, 220) . ' |
|                      | Message              | ' . str_pad($exception3->getMessage(), 220) . ' |
|                      | Code                 | ' . str_pad((string) $exception3->getCode(), 220) . ' |
|                      | File                 | ' . str_pad($exception3->getFile(), 220) . ' |
|                      | Line                 | ' . str_pad((string) $exception3->getLine(), 220) . ' |
|                      | Trace                | ' . str_pad($trace3[0], 220) . ' |
';
        for ($i = 1, $count = count($trace3); $i < $count; ++$i) {
            $expected .= '|                      |                      | ' . str_pad($trace3[$i], 220) . ' |
';
        }

        $expected .= '|   previous Throwable | Type                 | ' . str_pad($exception2::class, 220) . ' |
|                      | Message              | ' . str_pad($exception2->getMessage(), 220) . ' |
|                      | Code                 | ' . str_pad((string) $exception2->getCode(), 220) . ' |
|                      | File                 | ' . str_pad($exception2->getFile(), 220) . ' |
|                      | Line                 | ' . str_pad((string) $exception2->getLine(), 220) . ' |
|                      | Trace                | ' . str_pad($trace2[0], 220) . ' |
';
        for ($i = 1, $count = count($trace2); $i < $count; ++$i) {
            $expected .= '|                      |                      | ' . str_pad($trace2[$i], 220) . ' |
';
        }

        $expected .= '|   previous Throwable | Type                 | ' . str_pad($exception1::class, 220) . ' |
|                      | Message              | ' . str_pad($exception1->getMessage(), 220) . ' |
|                      | Code                 | ' . str_pad((string) $exception1->getCode(), 220) . ' |
|                      | File                 | ' . str_pad($exception1->getFile(), 220) . ' |
|                      | Line                 | ' . str_pad((string) $exception1->getLine(), 220) . ' |
|                      | Trace                | ' . str_pad($trace1[0], 220) . ' |
';
        for ($i = 1, $count = count($trace1); $i < $count; ++$i) {
            $expected .= '|                      |                      | ' . str_pad($trace1[$i], 220) . ' |
';
        }

        $expected .= '+----------------------+----------------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| Context                                                                                                                                                                                                                                                                    |
+----------------------+----------------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
|                  one | NULL                                                                                                                                                                                                                                                |
|                  two | true                                                                                                                                                                                                                                                |
|                three | false                                                                                                                                                                                                                                               |
|                 four | 0                    | abc                                                                                                                                                                                                                          |
|                      | 1                    | xyz                                                                                                                                                                                                                          |
|                 five | test                                                                                                                                                                                                                                                |
|                      | test                                                                                                                                                                                                                                                |
+----------------------+----------------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+

';

        self::assertSame($expected, $formatted);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function testFormat10(): void
    {
        $message  = 'test message';
        $channel  = 'test-channel';
        $datetime = new DateTimeImmutable('now');

        $exception1 = new RuntimeException('error');
        $exception2 = new UnexpectedValueException('error', 4711, $exception1);
        $exception3 = new OutOfRangeException('error', 1234, $exception2);

        $trace1 = explode(PHP_EOL, $exception1->getTraceAsString());
        $trace2 = explode(PHP_EOL, $exception2->getTraceAsString());
        $trace3 = explode(PHP_EOL, $exception3->getTraceAsString());

        $formatter = new StreamFormatter('%message% context.one %context.five% %extra.app% extra.Exception', 'default', null, true);

        $record = new LogRecord(
            datetime: $datetime,
            channel: $channel,
            level: Level::Error,
            message: $message,
            context: ['one' => null, 'two' => true, 'three' => false, 'four' => ['abc', 'xyz'], 'five' => "test\ntest"],
            extra: ['app' => 'test-app', 'Exception' => $exception3],
        );

        $formatted = $formatter->format($record);

        $expected = '============================================================================================================================================================================================================================

test message context.one test
test test-app extra.Exception


+----------------------+----------------------+------------------------------------------------------------------------------------ ERROR -----------------------------------------------------------------------------------------------------------------------------------+
| General Info                                                                                                                                                                                                                                                               |
+----------------------+----------------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
|                 Time | ' . $datetime->format(NormalizerFormatter::SIMPLE_DATE) . '                                                                                                                                                                                                                           |
|                Level | ERROR                                                                                                                                                                                                                                               |
+----------------------+----------------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| Extra                                                                                                                                                                                                                                                                      |
+----------------------+----------------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
|                  app | test-app                                                                                                                                                                                                                                            |
|            Exception | Type                 | ' . str_pad($exception3::class, 220) . ' |
|                      | Message              | ' . str_pad($exception3->getMessage(), 220) . ' |
|                      | Code                 | ' . str_pad((string) $exception3->getCode(), 220) . ' |
|                      | File                 | ' . str_pad($exception3->getFile(), 220) . ' |
|                      | Line                 | ' . str_pad((string) $exception3->getLine(), 220) . ' |
|                      | Trace                | ' . str_pad($trace3[0], 220) . ' |
';
        for ($i = 1, $count = count($trace3); $i < $count; ++$i) {
            $expected .= '|                      |                      | ' . str_pad($trace3[$i], 220) . ' |
';
        }

        $expected .= '|   previous Throwable | Type                 | ' . str_pad($exception2::class, 220) . ' |
|                      | Message              | ' . str_pad($exception2->getMessage(), 220) . ' |
|                      | Code                 | ' . str_pad((string) $exception2->getCode(), 220) . ' |
|                      | File                 | ' . str_pad($exception2->getFile(), 220) . ' |
|                      | Line                 | ' . str_pad((string) $exception2->getLine(), 220) . ' |
|                      | Trace                | ' . str_pad($trace2[0], 220) . ' |
';
        for ($i = 1, $count = count($trace2); $i < $count; ++$i) {
            $expected .= '|                      |                      | ' . str_pad($trace2[$i], 220) . ' |
';
        }

        $expected .= '|   previous Throwable | Type                 | ' . str_pad($exception1::class, 220) . ' |
|                      | Message              | ' . str_pad($exception1->getMessage(), 220) . ' |
|                      | Code                 | ' . str_pad((string) $exception1->getCode(), 220) . ' |
|                      | File                 | ' . str_pad($exception1->getFile(), 220) . ' |
|                      | Line                 | ' . str_pad((string) $exception1->getLine(), 220) . ' |
|                      | Trace                | ' . str_pad($trace1[0], 220) . ' |
';
        for ($i = 1, $count = count($trace1); $i < $count; ++$i) {
            $expected .= '|                      |                      | ' . str_pad($trace1[$i], 220) . ' |
';
        }

        $expected .= '+----------------------+----------------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| Context                                                                                                                                                                                                                                                                    |
+----------------------+----------------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
|                  one | NULL                                                                                                                                                                                                                                                |
|                  two | true                                                                                                                                                                                                                                                |
|                three | false                                                                                                                                                                                                                                               |
|                 four | 0                    | abc                                                                                                                                                                                                                          |
|                      | 1                    | xyz                                                                                                                                                                                                                          |
|                 five | test                                                                                                                                                                                                                                                |
|                      | test                                                                                                                                                                                                                                                |
+----------------------+----------------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+

';

        self::assertSame($expected, $formatted);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function testFormat11(): void
    {
        $message  = 'test message';
        $channel  = 'test-channel';
        $datetime = new DateTimeImmutable('now');

        $formatter = new StreamFormatter('%message% %context.one% %context.five% %context% %extra.app% %extra.app% %extra%', 'default', null, true);

        $record = new LogRecord(
            datetime: $datetime,
            channel: $channel,
            level: Level::Error,
            message: $message,
            context: ['one' => null, 'five' => "test\ntest"],
            extra: ['app' => 'test-app'],
        );

        $formatted = $formatter->format($record);

        $expected = '============================================================================================================================================================================================================================

test message NULL test
test  test-app test-app


+----------------------+----------------------+------------------------------------------------------------------------------------ ERROR -----------------------------------------------------------------------------------------------------------------------------------+
| General Info                                                                                                                                                                                                                                                               |
+----------------------+----------------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
|                 Time | ' . $datetime->format(NormalizerFormatter::SIMPLE_DATE) . '                                                                                                                                                                                                                           |
|                Level | ERROR                                                                                                                                                                                                                                               |
+----------------------+----------------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| Extra                                                                                                                                                                                                                                                                      |
+----------------------+----------------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
|                  app | test-app                                                                                                                                                                                                                                            |
+----------------------+----------------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| Context                                                                                                                                                                                                                                                                    |
+----------------------+----------------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
|                  one | NULL                                                                                                                                                                                                                                                |
|                 five | test                                                                                                                                                                                                                                                |
|                      | test                                                                                                                                                                                                                                                |
+----------------------+----------------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+

';

        self::assertSame($expected, $formatted);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function testFormat12(): void
    {
        $message          = 'test message';
        $channel          = 'test-channel';
        $datetime         = new DateTimeImmutable('now');
        $formattedMessage = 'this is a formatted message';
        $stdClass         = new stdClass();
        $stdClass->a      = $channel;
        $stdClass->b      = $message;

        $formatter = new StreamFormatter('%message% %context.one% %context.five% %context% %extra.app% %extra.app% %extra%', 'default', null, true);

        $record = new LogRecord(
            datetime: $datetime,
            channel: $channel,
            level: Level::Error,
            message: $message,
            context: ['one' => null, 'five' => "test\ntest", 'six' => $stdClass],
            extra: ['app' => 'test-app'],
        );

        $lineFormatter = $this->getMockBuilder(LineFormatter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $lineFormatter->expects(self::once())
            ->method('format')
            ->with($record)
            ->willReturn($formattedMessage);

        $formatter->setFormatter($lineFormatter);

        $formatted = $formatter->format($record);

        $expected = '============================================================================================================================================================================================================================

this is a formatted message


+----------------------+----------------------+------------------------------------------------------------------------------------ ERROR -----------------------------------------------------------------------------------------------------------------------------------+
| General Info                                                                                                                                                                                                                                                               |
+----------------------+----------------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
|                 Time | ' . $datetime->format(NormalizerFormatter::SIMPLE_DATE) . '                                                                                                                                                                                                                           |
|                Level | ERROR                                                                                                                                                                                                                                               |
+----------------------+----------------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| Extra                                                                                                                                                                                                                                                                      |
+----------------------+----------------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
|                  app | test-app                                                                                                                                                                                                                                            |
+----------------------+----------------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| Context                                                                                                                                                                                                                                                                    |
+----------------------+----------------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
|                  one | NULL                                                                                                                                                                                                                                                |
|                 five | test                                                                                                                                                                                                                                                |
|                      | test                                                                                                                                                                                                                                                |
|                  six | stdClass             | {"a":"test-channel","b":"test message"}                                                                                                                                                                                      |
+----------------------+----------------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+

';

        self::assertSame($expected, $formatted);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function testFormatBatch(): void
    {
        $message  = 'test message';
        $channel  = 'test-channel';
        $datetime = new DateTimeImmutable('now');

        $record1 = new LogRecord(
            datetime: $datetime,
            channel: $channel,
            level: Level::Error,
            message: $message,
            context: [],
            extra: [],
        );
        $record2 = new LogRecord(
            datetime: $datetime,
            channel: $channel,
            level: Level::Error,
            message: $message,
            context: ['one' => null, 'two' => true, 'three' => false, 'four' => ['abc', 'xyz']],
            extra: ['app' => 'test-app'],
        );
        $record3 = new LogRecord(
            datetime: $datetime,
            channel: $channel,
            level: Level::Error,
            message: $message,
            context: ['one' => null, 'two' => true, 'three' => false, 'four' => ['abc', 'xyz'], 'five' => "test\ntest"],
            extra: ['app' => 'test-app'],
        );

        $formatter = new StreamFormatter();
        $formatted = $formatter->formatBatch([$record1, $record2, $record3]);

        $expected = '============================================================================================================================================================================================================================

test message


┌──────────────────────┬──────────────────────┬──────────────────────────────────────────────────────────────────────────────────── ERROR ───────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┐
│ General Info                                                                                                                                                                                                                                                               │
├──────────────────────┼──────────────────────┼──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│                 Time │ ' . $datetime->format(NormalizerFormatter::SIMPLE_DATE) . '                                                                                                                                                                                                                           │
│                Level │ ERROR                                                                                                                                                                                                                                               │
└──────────────────────┴──────────────────────┴──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┘

============================================================================================================================================================================================================================

test message


┌──────────────────────┬──────────────────────┬──────────────────────────────────────────────────────────────────────────────────── ERROR ───────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┐
│ General Info                                                                                                                                                                                                                                                               │
├──────────────────────┼──────────────────────┼──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│                 Time │ ' . $datetime->format(NormalizerFormatter::SIMPLE_DATE) . '                                                                                                                                                                                                                           │
│                Level │ ERROR                                                                                                                                                                                                                                               │
├──────────────────────┼──────────────────────┼──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ Extra                                                                                                                                                                                                                                                                      │
├──────────────────────┼──────────────────────┼──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│                  app │ test-app                                                                                                                                                                                                                                            │
├──────────────────────┼──────────────────────┼──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ Context                                                                                                                                                                                                                                                                    │
├──────────────────────┼──────────────────────┼──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│                  one │ NULL                                                                                                                                                                                                                                                │
│                  two │ true                                                                                                                                                                                                                                                │
│                three │ false                                                                                                                                                                                                                                               │
│                 four │ 0                    │ abc                                                                                                                                                                                                                          │
│                      │ 1                    │ xyz                                                                                                                                                                                                                          │
└──────────────────────┴──────────────────────┴──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┘

============================================================================================================================================================================================================================

test message


┌──────────────────────┬──────────────────────┬──────────────────────────────────────────────────────────────────────────────────── ERROR ───────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┐
│ General Info                                                                                                                                                                                                                                                               │
├──────────────────────┼──────────────────────┼──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│                 Time │ ' . $datetime->format(NormalizerFormatter::SIMPLE_DATE) . '                                                                                                                                                                                                                           │
│                Level │ ERROR                                                                                                                                                                                                                                               │
├──────────────────────┼──────────────────────┼──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ Extra                                                                                                                                                                                                                                                                      │
├──────────────────────┼──────────────────────┼──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│                  app │ test-app                                                                                                                                                                                                                                            │
├──────────────────────┼──────────────────────┼──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ Context                                                                                                                                                                                                                                                                    │
├──────────────────────┼──────────────────────┼──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│                  one │ NULL                                                                                                                                                                                                                                                │
│                  two │ true                                                                                                                                                                                                                                                │
│                three │ false                                                                                                                                                                                                                                               │
│                 four │ 0                    │ abc                                                                                                                                                                                                                          │
│                      │ 1                    │ xyz                                                                                                                                                                                                                          │
│                 five │ test                                                                                                                                                                                                                                                │
│                      │ test                                                                                                                                                                                                                                                │
└──────────────────────┴──────────────────────┴──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┘

';

        self::assertSame($expected, $formatted);
    }
}
