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


┌──────────────────────┬──────────────────────┬──── ERROR ───────────────────────────────────────────────────┐
│ General Info                                                                                               │
├──────────────────────┼──────────────────────┼──────────────────────────────────────────────────────────────┤
│ Time                 │ ' . $datetime->format(StreamFormatter::SIMPLE_DATE) . '                                                           │
│ Level                │ ERROR                                                                               │
└──────────────────────┴──────────────────────┴──────────────────────────────────────────────────────────────┘

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


┌──────────────────────┬──────────────────────┬──── ERROR ───────────────────────────────────────────────────┐
│ General Info                                                                                               │
├──────────────────────┼──────────────────────┼──────────────────────────────────────────────────────────────┤
│ Time                 │ ' . $datetime->format(StreamFormatter::SIMPLE_DATE) . '                                                           │
│ Level                │ ERROR                                                                               │
├──────────────────────┼──────────────────────┼──────────────────────────────────────────────────────────────┤
│ Extra                                                                                                      │
├──────────────────────┼──────────────────────┼──────────────────────────────────────────────────────────────┤
│ App                  │ test-app                                                                            │
├──────────────────────┼──────────────────────┼──────────────────────────────────────────────────────────────┤
│ Context                                                                                                    │
├──────────────────────┼──────────────────────┼──────────────────────────────────────────────────────────────┤
│ One                  │ NULL                                                                                │
│ Two                  │ true                                                                                │
│ Three                │ false                                                                               │
│ Four                 │ 0                    │ abc                                                          │
│                      │ 1                    │ xyz                                                          │
└──────────────────────┴──────────────────────┴──────────────────────────────────────────────────────────────┘

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


┌──────────────────────┬──────────────────────┬──── ERROR ───────────────────────────────────────────────────┐
│ General Info                                                                                               │
├──────────────────────┼──────────────────────┼──────────────────────────────────────────────────────────────┤
│ Time                 │ ' . $datetime->format(StreamFormatter::SIMPLE_DATE) . '                                                           │
│ Level                │ ERROR                                                                               │
├──────────────────────┼──────────────────────┼──────────────────────────────────────────────────────────────┤
│ Context                                                                                                    │
├──────────────────────┼──────────────────────┼──────────────────────────────────────────────────────────────┤
│ One                  │ NULL                                                                                │
│ Three                │ false                                                                               │
│ Four                 │ 0                    │ abc                                                          │
│                      │ 1                    │ xyz                                                          │
└──────────────────────┴──────────────────────┴──────────────────────────────────────────────────────────────┘

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


┌──────────────────────┬──────────────────────┬──── ERROR ───────────────────────────────────────────────────┐
│ General Info                                                                                               │
├──────────────────────┼──────────────────────┼──────────────────────────────────────────────────────────────┤
│ Time                 │ ' . $datetime->format(StreamFormatter::SIMPLE_DATE) . '                                                           │
│ Level                │ ERROR                                                                               │
├──────────────────────┼──────────────────────┼──────────────────────────────────────────────────────────────┤
│ Context                                                                                                    │
├──────────────────────┼──────────────────────┼──────────────────────────────────────────────────────────────┤
│ One                  │ NULL                                                                                │
│ Two                  │ true                                                                                │
│ Three                │ false                                                                               │
└──────────────────────┴──────────────────────┴──────────────────────────────────────────────────────────────┘

';

        self::assertSame($expected, $formatted);
    }
}
