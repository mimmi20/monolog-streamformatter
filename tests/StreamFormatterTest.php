<?php
/**
 * This file is part of the mimmi20/monolog-streamformatter package.
 *
 * Copyright (c) 2022, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Mimmi20Test\Monolog\Formatter;

use DateTimeImmutable;
use Mimmi20\Monolog\Formatter\StreamFormatter;
use Monolog\Formatter\NormalizerFormatter;
use Monolog\Logger;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use ReflectionProperty;
use RuntimeException;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

use function array_map;
use function count;
use function explode;
use function implode;
use function str_pad;

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
        $ailb->setAccessible(true);

        self::assertFalse($ailb->getValue($formatter));

        $format = new ReflectionProperty($formatter, 'format');
        $format->setAccessible(true);

        self::assertSame(StreamFormatter::SIMPLE_FORMAT, $format->getValue($formatter));

        $st = new ReflectionProperty($formatter, 'includeStacktraces');
        $st->setAccessible(true);

        self::assertFalse($st->getValue($formatter));

        $ts = new ReflectionProperty($formatter, 'tableStyle');
        $ts->setAccessible(true);

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
        $ailb->setAccessible(true);

        self::assertTrue($ailb->getValue($formatter));

        $formatP = new ReflectionProperty($formatter, 'format');
        $formatP->setAccessible(true);

        self::assertSame($format, $formatP->getValue($formatter));

        $st = new ReflectionProperty($formatter, 'includeStacktraces');
        $st->setAccessible(true);

        self::assertFalse($st->getValue($formatter));

        $ts = new ReflectionProperty($formatter, 'tableStyle');
        $ts->setAccessible(true);

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
        $ailb->setAccessible(true);

        self::assertTrue($ailb->getValue($formatter));

        $formatP = new ReflectionProperty($formatter, 'format');
        $formatP->setAccessible(true);

        self::assertSame($format, $formatP->getValue($formatter));

        $st = new ReflectionProperty($formatter, 'includeStacktraces');
        $st->setAccessible(true);

        self::assertTrue($st->getValue($formatter));

        $ts = new ReflectionProperty($formatter, 'tableStyle');
        $ts->setAccessible(true);

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
        $ailb->setAccessible(true);

        self::assertFalse($ailb->getValue($formatter));

        $formatter->allowInlineLineBreaks();

        $ailb = new ReflectionProperty($formatter, 'allowInlineLineBreaks');
        $ailb->setAccessible(true);

        self::assertTrue($ailb->getValue($formatter));

        $formatP = new ReflectionProperty($formatter, 'format');
        $formatP->setAccessible(true);

        self::assertSame($format, $formatP->getValue($formatter));

        $st = new ReflectionProperty($formatter, 'includeStacktraces');
        $st->setAccessible(true);

        self::assertFalse($st->getValue($formatter));

        $ts = new ReflectionProperty($formatter, 'tableStyle');
        $ts->setAccessible(true);

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
        $ailb->setAccessible(true);

        self::assertTrue($ailb->getValue($formatter));

        $formatter->allowInlineLineBreaks(false);

        $ailb = new ReflectionProperty($formatter, 'allowInlineLineBreaks');
        $ailb->setAccessible(true);

        self::assertFalse($ailb->getValue($formatter));

        $formatP = new ReflectionProperty($formatter, 'format');
        $formatP->setAccessible(true);

        self::assertSame($format, $formatP->getValue($formatter));

        $st = new ReflectionProperty($formatter, 'includeStacktraces');
        $st->setAccessible(true);

        self::assertFalse($st->getValue($formatter));

        $formatter->includeStacktraces();

        $ailb = new ReflectionProperty($formatter, 'allowInlineLineBreaks');
        $ailb->setAccessible(true);

        self::assertTrue($ailb->getValue($formatter));

        $st = new ReflectionProperty($formatter, 'includeStacktraces');
        $st->setAccessible(true);

        self::assertTrue($st->getValue($formatter));

        $ts = new ReflectionProperty($formatter, 'tableStyle');
        $ts->setAccessible(true);

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
        $formatted = $formatter->format(['message' => $message, 'context' => [], 'level' => Logger::ERROR, 'level_name' => 'ERROR', 'channel' => $channel, 'datetime' => $datetime, 'extra' => []]);

        $expected = '============================================================================================================================================================================================================================

test message


┌──────────────────────┬──────────────────────┬──────────────────────────────────────────────────────────────────────────────────── ERROR ───────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┐
│ General Info                                                                                                                                                                                                                                                               │
├──────────────────────┼──────────────────────┼──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│                 Time │ ' . $datetime->format(StreamFormatter::SIMPLE_DATE) . '                                                                                                                                                                                                                           │
│                Level │ ERROR                                                                                                                                                                                                                                               │
└──────────────────────┴──────────────────────┴──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┘

';

        self::assertSame($expected, $formatted);
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
        $formatted = $formatter->format(['message' => $message, 'context' => ['one' => null, 'two' => true, 'three' => false, 'four' => ['abc', 'xyz']], 'level' => Logger::ERROR, 'level_name' => 'ERROR', 'channel' => $channel, 'datetime' => $datetime, 'extra' => ['app' => 'test-app']]);

        $expected = '============================================================================================================================================================================================================================

test message


┌──────────────────────┬──────────────────────┬──────────────────────────────────────────────────────────────────────────────────── ERROR ───────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┐
│ General Info                                                                                                                                                                                                                                                               │
├──────────────────────┼──────────────────────┼──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│                 Time │ ' . $datetime->format(StreamFormatter::SIMPLE_DATE) . '                                                                                                                                                                                                                           │
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
        $formatted = $formatter->format(['message' => $message, 'context' => ['one' => null, 'two' => true, 'three' => false, 'four' => ['abc', 'xyz']], 'level' => Logger::ERROR, 'level_name' => 'ERROR', 'channel' => $channel, 'datetime' => $datetime, 'extra' => ['app' => 'test-app']]);

        $expected = '============================================================================================================================================================================================================================

test message true test-app


┌──────────────────────┬──────────────────────┬──────────────────────────────────────────────────────────────────────────────────── ERROR ───────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┐
│ General Info                                                                                                                                                                                                                                                               │
├──────────────────────┼──────────────────────┼──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│                 Time │ ' . $datetime->format(StreamFormatter::SIMPLE_DATE) . '                                                                                                                                                                                                                           │
│                Level │ ERROR                                                                                                                                                                                                                                               │
├──────────────────────┼──────────────────────┼──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ Context                                                                                                                                                                                                                                                                    │
├──────────────────────┼──────────────────────┼──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│                  one │ NULL                                                                                                                                                                                                                                                │
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
        $formatted = $formatter->format(['message' => $message, 'context' => ['one' => null, 'two' => true, 'three' => false, 'four' => ['abc', 'xyz']], 'level' => Logger::ERROR, 'level_name' => 'ERROR', 'channel' => $channel, 'datetime' => $datetime, 'extra' => ['app' => 'test-app']]);

        $expected = '============================================================================================================================================================================================================================

test message ["abc","xyz"] test-app


┌──────────────────────┬──────────────────────┬──────────────────────────────────────────────────────────────────────────────────── ERROR ───────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┐
│ General Info                                                                                                                                                                                                                                                               │
├──────────────────────┼──────────────────────┼──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│                 Time │ ' . $datetime->format(StreamFormatter::SIMPLE_DATE) . '                                                                                                                                                                                                                           │
│                Level │ ERROR                                                                                                                                                                                                                                               │
├──────────────────────┼──────────────────────┼──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ Context                                                                                                                                                                                                                                                                    │
├──────────────────────┼──────────────────────┼──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│                  one │ NULL                                                                                                                                                                                                                                                │
│                  two │ true                                                                                                                                                                                                                                                │
│                three │ false                                                                                                                                                                                                                                               │
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
        $formatted = $formatter->format(['message' => $message, 'context' => ['one' => null, 'two' => true, 'three' => false, 'four' => ['abc', 'xyz'], 'five' => "test\ntest"], 'level' => Logger::ERROR, 'level_name' => 'ERROR', 'channel' => $channel, 'datetime' => $datetime, 'extra' => ['app' => 'test-app']]);

        $expected = '============================================================================================================================================================================================================================

test message test test test-app


+----------------------+----------------------+------------------------------------------------------------------------------------ ERROR -----------------------------------------------------------------------------------------------------------------------------------+
| General Info                                                                                                                                                                                                                                                               |
+----------------------+----------------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
|                 Time | ' . $datetime->format(StreamFormatter::SIMPLE_DATE) . '                                                                                                                                                                                                                           |
|                Level | ERROR                                                                                                                                                                                                                                               |
+----------------------+----------------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| Context                                                                                                                                                                                                                                                                    |
+----------------------+----------------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
|                  one | NULL                                                                                                                                                                                                                                                |
|                  two | true                                                                                                                                                                                                                                                |
|                three | false                                                                                                                                                                                                                                               |
|                 four | 0                    | abc                                                                                                                                                                                                                          |
|                      | 1                    | xyz                                                                                                                                                                                                                          |
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
        $formatted = $formatter->format(['message' => $message, 'context' => ['one' => null, 'two' => true, 'three' => false, 'four' => ['abc', 'xyz'], 'five' => "test\ntest"], 'level' => Logger::ERROR, 'level_name' => 'ERROR', 'channel' => $channel, 'datetime' => $datetime, 'extra' => ['app' => 'test-app']]);

        $expected = '============================================================================================================================================================================================================================

test message test
test test-app


+----------------------+----------------------+------------------------------------------------------------------------------------ ERROR -----------------------------------------------------------------------------------------------------------------------------------+
| General Info                                                                                                                                                                                                                                                               |
+----------------------+----------------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
|                 Time | ' . $datetime->format(StreamFormatter::SIMPLE_DATE) . '                                                                                                                                                                                                                           |
|                Level | ERROR                                                                                                                                                                                                                                               |
+----------------------+----------------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| Context                                                                                                                                                                                                                                                                    |
+----------------------+----------------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
|                  one | NULL                                                                                                                                                                                                                                                |
|                  two | true                                                                                                                                                                                                                                                |
|                three | false                                                                                                                                                                                                                                               |
|                 four | 0                    | abc                                                                                                                                                                                                                          |
|                      | 1                    | xyz                                                                                                                                                                                                                          |
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
        $message  = 'test message';
        $channel  = 'test-channel';
        $datetime = new DateTimeImmutable('now');

        $exception = new RuntimeException('error');

        $formattedTrace = array_map(
            static fn (array $entry): string => ($entry['file'] ?? '') . ':' . ($entry['line'] ?? ''),
            $exception->getTrace()
        );

        $formatter = new StreamFormatter('%message% %context.five% <%extra.Exception%>', 'default', null, true);
        $formatted = $formatter->format(['message' => $message, 'context' => ['one' => null, 'two' => true, 'three' => false, 'four' => ['abc', 'xyz'], 'five' => "test\ntest"], 'level' => Logger::ERROR, 'level_name' => 'ERROR', 'channel' => $channel, 'datetime' => $datetime, 'extra' => ['app' => 'test-app', 'Exception' => $exception]]);

        $expected = '============================================================================================================================================================================================================================

test message test
test <{"class":"RuntimeException","message":"error","code":0,"file":"' . $exception->getFile() . ':' . $exception->getLine() . '","trace":["' . implode('","', $formattedTrace) . '"]}>


+----------------------+----------------------+------------------------------------------------------------------------------------ ERROR -----------------------------------------------------------------------------------------------------------------------------------+
| General Info                                                                                                                                                                                                                                                               |
+----------------------+----------------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
|                 Time | ' . $datetime->format(StreamFormatter::SIMPLE_DATE) . '                                                                                                                                                                                                                           |
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
        $formatted = $formatter->format(['message' => $message, 'context' => ['one' => null, 'two' => true, 'three' => false, 'four' => ['abc', 'xyz'], 'five' => "test\ntest"], 'level' => Logger::ERROR, 'level_name' => 'ERROR', 'channel' => $channel, 'datetime' => $datetime, 'extra' => ['app' => 'test-app', 'Exception' => $exception]]);

        $expected = '============================================================================================================================================================================================================================

test message test
test test-app


+----------------------+----------------------+------------------------------------------------------------------------------------ ERROR -----------------------------------------------------------------------------------------------------------------------------------+
| General Info                                                                                                                                                                                                                                                               |
+----------------------+----------------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
|                 Time | ' . $datetime->format(StreamFormatter::SIMPLE_DATE) . '                                                                                                                                                                                                                           |
|                Level | ERROR                                                                                                                                                                                                                                               |
+----------------------+----------------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| Extra                                                                                                                                                                                                                                                                      |
+----------------------+----------------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
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
+----------------------+----------------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+

';

        self::assertSame($expected, $formatted);
    }
}
