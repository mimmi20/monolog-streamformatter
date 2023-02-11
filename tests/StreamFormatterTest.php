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
use stdClass;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\Output;
use UnexpectedValueException;

use function explode;
use function str_repeat;
use function str_replace;

use const PHP_EOL;

final class StreamFormatterTest extends TestCase
{
    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function testConstructWithDefaults(): void
    {
        $output = $this->getMockBuilder(BufferedOutput::class)
            ->disableOriginalConstructor()
            ->getMock();
        $output->expects(self::never())
            ->method('fetch');
        $output->expects(self::never())
            ->method('writeln');

        $table = $this->getMockBuilder(Table::class)
            ->disableOriginalConstructor()
            ->getMock();
        $table->expects(self::once())
            ->method('setStyle')
            ->with(StreamFormatter::BOX_STYLE)
            ->willReturnSelf();
        $table->expects(self::exactly(3))
            ->method('setColumnMaxWidth')
            ->willReturnSelf();
        $table->expects(self::once())
            ->method('setColumnWidths')
            ->with([20, 20, 220])
            ->willReturnSelf();
        $table->expects(self::never())
            ->method('setRows');
        $table->expects(self::never())
            ->method('addRow');
        $table->expects(self::never())
            ->method('render');

        $formatter = new StreamFormatter($output, $table);

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
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function testConstructWithValues(): void
    {
        $format     = '[%level_name%] %message%';
        $tableStyle = 'test-style';
        $dateFormat = 'c';

        $output = $this->getMockBuilder(BufferedOutput::class)
            ->disableOriginalConstructor()
            ->getMock();
        $output->expects(self::never())
            ->method('fetch');
        $output->expects(self::never())
            ->method('writeln');

        $table = $this->getMockBuilder(Table::class)
            ->disableOriginalConstructor()
            ->getMock();
        $table->expects(self::once())
            ->method('setStyle')
            ->with($tableStyle)
            ->willReturnSelf();
        $table->expects(self::exactly(3))
            ->method('setColumnMaxWidth')
            ->willReturnSelf();
        $table->expects(self::once())
            ->method('setColumnWidths')
            ->with([20, 20, 220])
            ->willReturnSelf();
        $table->expects(self::never())
            ->method('setRows');
        $table->expects(self::never())
            ->method('addRow');
        $table->expects(self::never())
            ->method('render');

        $formatter = new StreamFormatter($output, $table, $format, $tableStyle, $dateFormat, true, false);

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
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function testConstructWithValues2(): void
    {
        $format     = '[%level_name%] %message%';
        $tableStyle = 'test-style';
        $dateFormat = 'c';

        $output = $this->getMockBuilder(BufferedOutput::class)
            ->disableOriginalConstructor()
            ->getMock();
        $output->expects(self::never())
            ->method('fetch');
        $output->expects(self::never())
            ->method('writeln');

        $table = $this->getMockBuilder(Table::class)
            ->disableOriginalConstructor()
            ->getMock();
        $table->expects(self::once())
            ->method('setStyle')
            ->with($tableStyle)
            ->willReturnSelf();
        $table->expects(self::exactly(3))
            ->method('setColumnMaxWidth')
            ->willReturnSelf();
        $table->expects(self::once())
            ->method('setColumnWidths')
            ->with([20, 20, 220])
            ->willReturnSelf();
        $table->expects(self::never())
            ->method('setRows');
        $table->expects(self::never())
            ->method('addRow');
        $table->expects(self::never())
            ->method('render');

        $formatter = new StreamFormatter($output, $table, $format, $tableStyle, $dateFormat, false, true);

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
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function testConstructWithValues3(): void
    {
        $format     = '[%level_name%] %message%';
        $tableStyle = 'test-style';
        $dateFormat = 'c';

        $output = $this->getMockBuilder(BufferedOutput::class)
            ->disableOriginalConstructor()
            ->getMock();
        $output->expects(self::never())
            ->method('fetch');
        $output->expects(self::never())
            ->method('writeln');

        $table = $this->getMockBuilder(Table::class)
            ->disableOriginalConstructor()
            ->getMock();
        $table->expects(self::once())
            ->method('setStyle')
            ->with($tableStyle)
            ->willReturnSelf();
        $table->expects(self::exactly(3))
            ->method('setColumnMaxWidth')
            ->willReturnSelf();
        $table->expects(self::once())
            ->method('setColumnWidths')
            ->with([20, 20, 220])
            ->willReturnSelf();
        $table->expects(self::never())
            ->method('setRows');
        $table->expects(self::never())
            ->method('addRow');
        $table->expects(self::never())
            ->method('render');

        $formatter = new StreamFormatter($output, $table, $format, $tableStyle, $dateFormat, false, false);

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
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function testConstructWithValues4(): void
    {
        $format     = '[%level_name%] %message%';
        $tableStyle = 'test-style';
        $dateFormat = 'c';

        $output = $this->getMockBuilder(BufferedOutput::class)
            ->disableOriginalConstructor()
            ->getMock();
        $output->expects(self::never())
            ->method('fetch');
        $output->expects(self::never())
            ->method('writeln');

        $table = $this->getMockBuilder(Table::class)
            ->disableOriginalConstructor()
            ->getMock();
        $table->expects(self::once())
            ->method('setStyle')
            ->with($tableStyle)
            ->willReturnSelf();
        $table->expects(self::exactly(3))
            ->method('setColumnMaxWidth')
            ->willReturnSelf();
        $table->expects(self::once())
            ->method('setColumnWidths')
            ->with([20, 20, 220])
            ->willReturnSelf();
        $table->expects(self::never())
            ->method('setRows');
        $table->expects(self::never())
            ->method('addRow');
        $table->expects(self::never())
            ->method('render');

        $formatter = new StreamFormatter($output, $table, $format, $tableStyle, $dateFormat, true, false);

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
     * @throws RuntimeException
     */
    public function testFormat(): void
    {
        $message  = 'test message';
        $channel  = 'test-channel';
        $datetime = new DateTimeImmutable('now');

        $expected = 'rendered-content';

        $output = $this->getMockBuilder(BufferedOutput::class)
            ->disableOriginalConstructor()
            ->getMock();
        $output->expects(self::exactly(2))
            ->method('fetch')
            ->willReturnOnConsecutiveCalls('', $expected);
        $output->expects(self::exactly(5))
            ->method('writeln')
            ->willReturnMap(
                [
                    [str_repeat('=', 220), Output::OUTPUT_NORMAL, null],
                    ['', Output::OUTPUT_NORMAL, null],
                    [$message, Output::OUTPUT_NORMAL, null],
                ],
            );

        $table = $this->getMockBuilder(Table::class)
            ->disableOriginalConstructor()
            ->getMock();
        $table->expects(self::once())
            ->method('setStyle')
            ->with(StreamFormatter::BOX_STYLE)
            ->willReturnSelf();
        $table->expects(self::exactly(3))
            ->method('setColumnMaxWidth')
            ->willReturnSelf();
        $table->expects(self::once())
            ->method('setColumnWidths')
            ->with([20, 20, 220])
            ->willReturnSelf();
        $table->expects(self::once())
            ->method('setRows')
            ->with([])
            ->willReturnSelf();
        $table->expects(self::exactly(3))
            ->method('addRow');
        $table->expects(self::once())
            ->method('render');

        $formatter = new StreamFormatter($output, $table);

        $record = new LogRecord(
            datetime: $datetime,
            channel: $channel,
            level: Level::Error,
            message: $message,
            context: [],
            extra: [],
        );

        $formatted = $formatter->format($record);

        self::assertSame($expected, $formatted);
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     */
    public function testFormat2(): void
    {
        $message  = 'test message';
        $channel  = 'test-channel';
        $datetime = new DateTimeImmutable('now');

        $expected = 'rendered-content';

        $output = $this->getMockBuilder(BufferedOutput::class)
            ->disableOriginalConstructor()
            ->getMock();
        $output->expects(self::exactly(2))
            ->method('fetch')
            ->willReturnOnConsecutiveCalls('', $expected);
        $output->expects(self::exactly(5))
            ->method('writeln')
            ->willReturnMap(
                [
                    [str_repeat('=', 220), Output::OUTPUT_NORMAL, null],
                    ['', Output::OUTPUT_NORMAL, null],
                    [$message, Output::OUTPUT_NORMAL, null],
                ],
            );

        $table = $this->getMockBuilder(Table::class)
            ->disableOriginalConstructor()
            ->getMock();
        $table->expects(self::once())
            ->method('setStyle')
            ->with(StreamFormatter::BOX_STYLE)
            ->willReturnSelf();
        $table->expects(self::exactly(3))
            ->method('setColumnMaxWidth')
            ->willReturnSelf();
        $table->expects(self::once())
            ->method('setColumnWidths')
            ->with([20, 20, 220])
            ->willReturnSelf();
        $table->expects(self::once())
            ->method('setRows')
            ->with([])
            ->willReturnSelf();
        $table->expects(self::exactly(15))
            ->method('addRow');
        $table->expects(self::once())
            ->method('render');

        $formatter = new StreamFormatter($output, $table);

        $record = new LogRecord(
            datetime: $datetime,
            channel: $channel,
            level: Level::Error,
            message: $message,
            context: ['one' => null, 'two' => true, 'three' => false, 'four' => ['abc', 'xyz']],
            extra: ['app' => 'test-app'],
        );

        $formatted = $formatter->format($record);

        self::assertSame($expected, $formatted);
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     */
    public function testFormat3(): void
    {
        $message  = 'test message';
        $channel  = 'test-channel';
        $datetime = new DateTimeImmutable('now');

        $expected = 'rendered-content';

        $output = $this->getMockBuilder(BufferedOutput::class)
            ->disableOriginalConstructor()
            ->getMock();
        $output->expects(self::exactly(2))
            ->method('fetch')
            ->willReturnOnConsecutiveCalls('', $expected);
        $output->expects(self::exactly(5))
            ->method('writeln')
            ->willReturnMap(
                [
                    [str_repeat('=', 220), Output::OUTPUT_NORMAL, null],
                    ['', Output::OUTPUT_NORMAL, null],
                    [$message, Output::OUTPUT_NORMAL, null],
                ],
            );

        $table = $this->getMockBuilder(Table::class)
            ->disableOriginalConstructor()
            ->getMock();
        $table->expects(self::once())
            ->method('setStyle')
            ->with(StreamFormatter::BOX_STYLE)
            ->willReturnSelf();
        $table->expects(self::exactly(3))
            ->method('setColumnMaxWidth')
            ->willReturnSelf();
        $table->expects(self::once())
            ->method('setColumnWidths')
            ->with([20, 20, 220])
            ->willReturnSelf();
        $table->expects(self::once())
            ->method('setRows')
            ->with([])
            ->willReturnSelf();
        $table->expects(self::exactly(15))
            ->method('addRow');
        $table->expects(self::once())
            ->method('render');

        $formatter = new StreamFormatter($output, $table, '%message% %context.two% %extra.app%');

        $record = new LogRecord(
            datetime: $datetime,
            channel: $channel,
            level: Level::Error,
            message: $message,
            context: ['one' => null, 'two' => true, 'three' => false, 'four' => ['abc', 'xyz']],
            extra: ['app' => 'test-app'],
        );

        $formatted = $formatter->format($record);

        self::assertSame($expected, $formatted);
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     */
    public function testFormat4(): void
    {
        $message  = 'test message';
        $channel  = 'test-channel';
        $datetime = new DateTimeImmutable('now');

        $expected = 'rendered-content';

        $output = $this->getMockBuilder(BufferedOutput::class)
            ->disableOriginalConstructor()
            ->getMock();
        $output->expects(self::exactly(2))
            ->method('fetch')
            ->willReturnOnConsecutiveCalls('', $expected);
        $output->expects(self::exactly(5))
            ->method('writeln')
            ->willReturnMap(
                [
                    [str_repeat('=', 220), Output::OUTPUT_NORMAL, null],
                    ['', Output::OUTPUT_NORMAL, null],
                    [$message, Output::OUTPUT_NORMAL, null],
                ],
            );

        $table = $this->getMockBuilder(Table::class)
            ->disableOriginalConstructor()
            ->getMock();
        $table->expects(self::once())
            ->method('setStyle')
            ->with(StreamFormatter::BOX_STYLE)
            ->willReturnSelf();
        $table->expects(self::exactly(3))
            ->method('setColumnMaxWidth')
            ->willReturnSelf();
        $table->expects(self::once())
            ->method('setColumnWidths')
            ->with([20, 20, 220])
            ->willReturnSelf();
        $table->expects(self::once())
            ->method('setRows')
            ->with([])
            ->willReturnSelf();
        $table->expects(self::exactly(15))
            ->method('addRow');
        $table->expects(self::once())
            ->method('render');

        $formatter = new StreamFormatter($output, $table, '%message% %context.four% %extra.app%');

        $record = new LogRecord(
            datetime: $datetime,
            channel: $channel,
            level: Level::Error,
            message: $message,
            context: ['one' => null, 'two' => true, 'three' => false, 'four' => ['abc', 'xyz']],
            extra: ['app' => 'test-app'],
        );

        $formatted = $formatter->format($record);

        self::assertSame($expected, $formatted);
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     */
    public function testFormat5(): void
    {
        $message    = 'test message';
        $channel    = 'test-channel';
        $datetime   = new DateTimeImmutable('now');
        $tableStyle = 'default';

        $expected = 'rendered-content';

        $output = $this->getMockBuilder(BufferedOutput::class)
            ->disableOriginalConstructor()
            ->getMock();
        $output->expects(self::exactly(2))
            ->method('fetch')
            ->willReturnOnConsecutiveCalls('', $expected);
        $output->expects(self::exactly(5))
            ->method('writeln')
            ->willReturnMap(
                [
                    [str_repeat('=', 220), Output::OUTPUT_NORMAL, null],
                    ['', Output::OUTPUT_NORMAL, null],
                    [$message, Output::OUTPUT_NORMAL, null],
                ],
            );

        $table = $this->getMockBuilder(Table::class)
            ->disableOriginalConstructor()
            ->getMock();
        $table->expects(self::once())
            ->method('setStyle')
            ->with($tableStyle)
            ->willReturnSelf();
        $table->expects(self::exactly(3))
            ->method('setColumnMaxWidth')
            ->willReturnSelf();
        $table->expects(self::once())
            ->method('setColumnWidths')
            ->with([20, 20, 220])
            ->willReturnSelf();
        $table->expects(self::once())
            ->method('setRows')
            ->with([])
            ->willReturnSelf();
        $table->expects(self::exactly(16))
            ->method('addRow');
        $table->expects(self::once())
            ->method('render');

        $formatter = new StreamFormatter($output, $table, '%message% %context.five% %extra.app%', $tableStyle, null, false);

        $record = new LogRecord(
            datetime: $datetime,
            channel: $channel,
            level: Level::Error,
            message: $message,
            context: ['one' => null, 'two' => true, 'three' => false, 'four' => ['abc', 'xyz'], 'five' => "test\ntest"],
            extra: ['app' => 'test-app'],
        );

        $formatted = $formatter->format($record);

        self::assertSame($expected, $formatted);
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     */
    public function testFormat6(): void
    {
        $message    = 'test message';
        $channel    = 'test-channel';
        $datetime   = new DateTimeImmutable('now');
        $tableStyle = 'default';

        $expected = 'rendered-content';

        $output = $this->getMockBuilder(BufferedOutput::class)
            ->disableOriginalConstructor()
            ->getMock();
        $output->expects(self::exactly(2))
            ->method('fetch')
            ->willReturnOnConsecutiveCalls('', $expected);
        $output->expects(self::exactly(5))
            ->method('writeln')
            ->willReturnMap(
                [
                    [str_repeat('=', 220), Output::OUTPUT_NORMAL, null],
                    ['', Output::OUTPUT_NORMAL, null],
                    [$message, Output::OUTPUT_NORMAL, null],
                ],
            );

        $table = $this->getMockBuilder(Table::class)
            ->disableOriginalConstructor()
            ->getMock();
        $table->expects(self::once())
            ->method('setStyle')
            ->with($tableStyle)
            ->willReturnSelf();
        $table->expects(self::exactly(3))
            ->method('setColumnMaxWidth')
            ->willReturnSelf();
        $table->expects(self::once())
            ->method('setColumnWidths')
            ->with([20, 20, 220])
            ->willReturnSelf();
        $table->expects(self::once())
            ->method('setRows')
            ->with([])
            ->willReturnSelf();
        $table->expects(self::exactly(16))
            ->method('addRow');
        $table->expects(self::once())
            ->method('render');

        $formatter = new StreamFormatter($output, $table, '%message% %context.five% %extra.app%', $tableStyle, null, true);

        $record = new LogRecord(
            datetime: $datetime,
            channel: $channel,
            level: Level::Error,
            message: $message,
            context: ['one' => null, 'two' => true, 'three' => false, 'four' => ['abc', 'xyz'], 'five' => "test\ntest"],
            extra: ['app' => 'test-app'],
        );

        $formatted = $formatter->format($record);

        self::assertSame($expected, $formatted);
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     */
    public function testFormat7(): void
    {
        $message    = 'test message';
        $channel    = 'test-channel';
        $tableStyle = 'default';
        $datetime   = new DateTimeImmutable('now');
        $exception  = new RuntimeException('error');
        $trace      = explode(PHP_EOL, $exception->getTraceAsString());

        $expected = 'rendered-content';

        $output = $this->getMockBuilder(BufferedOutput::class)
            ->disableOriginalConstructor()
            ->getMock();
        $output->expects(self::exactly(2))
            ->method('fetch')
            ->willReturnOnConsecutiveCalls('', $expected);
        $output->expects(self::exactly(5))
            ->method('writeln')
            ->willReturnMap(
                [
                    [str_repeat('=', 220), Output::OUTPUT_NORMAL, null],
                    ['', Output::OUTPUT_NORMAL, null],
                    [$message, Output::OUTPUT_NORMAL, null],
                ],
            );

        $table = $this->getMockBuilder(Table::class)
            ->disableOriginalConstructor()
            ->getMock();
        $table->expects(self::once())
            ->method('setStyle')
            ->with($tableStyle)
            ->willReturnSelf();
        $table->expects(self::exactly(3))
            ->method('setColumnMaxWidth')
            ->willReturnSelf();
        $table->expects(self::once())
            ->method('setColumnWidths')
            ->with([20, 20, 220])
            ->willReturnSelf();
        $table->expects(self::once())
            ->method('setRows')
            ->with([])
            ->willReturnSelf();
        $table->expects(self::exactly(22))
            ->method('addRow');
        $table->expects(self::once())
            ->method('render');

        $formatter = new StreamFormatter($output, $table, '%message% %context.five% <%extra.Exception%>', $tableStyle, null, true);

        $record = new LogRecord(
            datetime: $datetime,
            channel: $channel,
            level: Level::Error,
            message: $message,
            context: ['one' => null, 'two' => true, 'three' => false, 'four' => ['abc', 'xyz'], 'five' => "test\ntest"],
            extra: ['app' => 'test-app', 'Exception' => $exception],
        );

        $formatted = $formatter->format($record);

        self::assertSame($expected, $formatted);
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     */
    public function testFormat8(): void
    {
        $message    = 'test message';
        $channel    = 'test-channel';
        $tableStyle = 'default';
        $datetime   = new DateTimeImmutable('now');

        $exception = new RuntimeException('error');

        $trace = explode(PHP_EOL, $exception->getTraceAsString());

        $expected = 'rendered-content';

        $output = $this->getMockBuilder(BufferedOutput::class)
            ->disableOriginalConstructor()
            ->getMock();
        $output->expects(self::exactly(2))
            ->method('fetch')
            ->willReturnOnConsecutiveCalls('', $expected);
        $output->expects(self::exactly(5))
            ->method('writeln')
            ->willReturnMap(
                [
                    [str_repeat('=', 220), Output::OUTPUT_NORMAL, null],
                    ['', Output::OUTPUT_NORMAL, null],
                    [$message, Output::OUTPUT_NORMAL, null],
                ],
            );

        $table = $this->getMockBuilder(Table::class)
            ->disableOriginalConstructor()
            ->getMock();
        $table->expects(self::once())
            ->method('setStyle')
            ->with($tableStyle)
            ->willReturnSelf();
        $table->expects(self::exactly(3))
            ->method('setColumnMaxWidth')
            ->willReturnSelf();
        $table->expects(self::once())
            ->method('setColumnWidths')
            ->with([20, 20, 220])
            ->willReturnSelf();
        $table->expects(self::once())
            ->method('setRows')
            ->with([])
            ->willReturnSelf();
        $table->expects(self::exactly(22))
            ->method('addRow');
        $table->expects(self::once())
            ->method('render');

        $formatter = new StreamFormatter($output, $table, '%message% %context.five% %extra.app%', $tableStyle, null, true);

        $record = new LogRecord(
            datetime: $datetime,
            channel: $channel,
            level: Level::Error,
            message: $message,
            context: ['one' => null, 'two' => true, 'three' => false, 'four' => ['abc', 'xyz'], 'five' => "test\ntest"],
            extra: ['app' => 'test-app', 'Exception' => $exception],
        );

        $formatted = $formatter->format($record);

        self::assertSame($expected, $formatted);
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     */
    public function testFormat9(): void
    {
        $message    = 'test message';
        $channel    = 'test-channel';
        $tableStyle = 'default';
        $datetime   = new DateTimeImmutable('now');

        $expected = 'rendered-content';

        $exception1 = new RuntimeException('error');
        $exception2 = new UnexpectedValueException('error', 4711, $exception1);
        $exception3 = new OutOfRangeException('error', 1234, $exception2);

        $trace1 = explode(PHP_EOL, $exception1->getTraceAsString());
        $trace2 = explode(PHP_EOL, $exception2->getTraceAsString());
        $trace3 = explode(PHP_EOL, $exception3->getTraceAsString());

        $output = $this->getMockBuilder(BufferedOutput::class)
            ->disableOriginalConstructor()
            ->getMock();
        $output->expects(self::exactly(2))
            ->method('fetch')
            ->willReturnOnConsecutiveCalls('', $expected);
        $output->expects(self::exactly(5))
            ->method('writeln')
            ->willReturnMap(
                [
                    [str_repeat('=', 220), Output::OUTPUT_NORMAL, null],
                    ['', Output::OUTPUT_NORMAL, null],
                    [$message, Output::OUTPUT_NORMAL, null],
                ],
            );

        $table = $this->getMockBuilder(Table::class)
            ->disableOriginalConstructor()
            ->getMock();
        $table->expects(self::once())
            ->method('setStyle')
            ->with($tableStyle)
            ->willReturnSelf();
        $table->expects(self::exactly(3))
            ->method('setColumnMaxWidth')
            ->willReturnSelf();
        $table->expects(self::once())
            ->method('setColumnWidths')
            ->with([20, 20, 220])
            ->willReturnSelf();
        $table->expects(self::once())
            ->method('setRows')
            ->with([])
            ->willReturnSelf();
        $table->expects(self::exactly(34))
            ->method('addRow');
        $table->expects(self::once())
            ->method('render');

        $formatter = new StreamFormatter($output, $table, '%message% %context.five% %extra.app%', $tableStyle, null, true);

        $record = new LogRecord(
            datetime: $datetime,
            channel: $channel,
            level: Level::Error,
            message: $message,
            context: ['one' => null, 'two' => true, 'three' => false, 'four' => ['abc', 'xyz'], 'five' => "test\ntest"],
            extra: ['app' => 'test-app', 'Exception' => $exception3],
        );

        $formatted = $formatter->format($record);

        self::assertSame($expected, $formatted);
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     */
    public function testFormat10(): void
    {
        $message    = 'test message';
        $channel    = 'test-channel';
        $tableStyle = 'default';
        $datetime   = new DateTimeImmutable('now');

        $exception1 = new RuntimeException('error');
        $exception2 = new UnexpectedValueException('error', 4711, $exception1);
        $exception3 = new OutOfRangeException('error', 1234, $exception2);

        $expected = 'rendered-content';

        $trace1 = explode("\n", str_replace("\r\n", "\n", $exception1->getTraceAsString()));
        $trace2 = explode("\n", str_replace("\r\n", "\n", $exception2->getTraceAsString()));
        $trace3 = explode("\n", str_replace("\r\n", "\n", $exception3->getTraceAsString()));

        $output = $this->getMockBuilder(BufferedOutput::class)
            ->disableOriginalConstructor()
            ->getMock();
        $output->expects(self::exactly(2))
            ->method('fetch')
            ->willReturnOnConsecutiveCalls('', $expected);
        $output->expects(self::exactly(5))
            ->method('writeln')
            ->willReturnMap(
                [
                    [str_repeat('=', 220), Output::OUTPUT_NORMAL, null],
                    ['', Output::OUTPUT_NORMAL, null],
                    [$message, Output::OUTPUT_NORMAL, null],
                ],
            );

        $table = $this->getMockBuilder(Table::class)
            ->disableOriginalConstructor()
            ->getMock();
        $table->expects(self::once())
            ->method('setStyle')
            ->with($tableStyle)
            ->willReturnSelf();
        $table->expects(self::exactly(3))
            ->method('setColumnMaxWidth')
            ->willReturnSelf();
        $table->expects(self::once())
            ->method('setColumnWidths')
            ->with([20, 20, 220])
            ->willReturnSelf();
        $table->expects(self::once())
            ->method('setRows')
            ->with([])
            ->willReturnSelf();
        $table->expects(self::exactly(34))
            ->method('addRow');
        $table->expects(self::once())
            ->method('render');

        $formatter = new StreamFormatter($output, $table, '%message% context.one %context.five% %extra.app% extra.Exception', $tableStyle, null, true);

        $record = new LogRecord(
            datetime: $datetime,
            channel: $channel,
            level: Level::Error,
            message: $message,
            context: ['one' => null, 'two' => true, 'three' => false, 'four' => ['abc', 'xyz'], 'five' => "test\ntest"],
            extra: ['app' => 'test-app', 'Exception' => $exception3],
        );

        $formatted = $formatter->format($record);

        self::assertSame($expected, $formatted);
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     */
    public function testFormat11(): void
    {
        $message    = 'test message';
        $channel    = 'test-channel';
        $tableStyle = 'default';
        $datetime   = new DateTimeImmutable('now');

        $expected = 'rendered-content';

        $output = $this->getMockBuilder(BufferedOutput::class)
            ->disableOriginalConstructor()
            ->getMock();
        $output->expects(self::exactly(2))
            ->method('fetch')
            ->willReturnOnConsecutiveCalls('', $expected);
        $output->expects(self::exactly(5))
            ->method('writeln')
            ->willReturnMap(
                [
                    [str_repeat('=', 220), Output::OUTPUT_NORMAL, null],
                    ['', Output::OUTPUT_NORMAL, null],
                    [$message, Output::OUTPUT_NORMAL, null],
                ],
            );

        $table = $this->getMockBuilder(Table::class)
            ->disableOriginalConstructor()
            ->getMock();
        $table->expects(self::once())
            ->method('setStyle')
            ->with($tableStyle)
            ->willReturnSelf();
        $table->expects(self::exactly(3))
            ->method('setColumnMaxWidth')
            ->willReturnSelf();
        $table->expects(self::once())
            ->method('setColumnWidths')
            ->with([20, 20, 220])
            ->willReturnSelf();
        $table->expects(self::once())
            ->method('setRows')
            ->with([])
            ->willReturnSelf();
        $table->expects(self::exactly(12))
            ->method('addRow');
        $table->expects(self::once())
            ->method('render');

        $formatter = new StreamFormatter($output, $table, '%message% %context.one% %context.five% %context% %extra.app% %extra.app% %extra%', $tableStyle, null, true);

        $record = new LogRecord(
            datetime: $datetime,
            channel: $channel,
            level: Level::Error,
            message: $message,
            context: ['one' => null, 'five' => "test\ntest"],
            extra: ['app' => 'test-app'],
        );

        $formatted = $formatter->format($record);

        self::assertSame($expected, $formatted);
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     */
    public function testFormat12(): void
    {
        $message          = 'test message';
        $channel          = 'test-channel';
        $tableStyle       = 'default';
        $datetime         = new DateTimeImmutable('now');
        $formattedMessage = 'this is a formatted message';
        $stdClass         = new stdClass();
        $stdClass->a      = $channel;
        $stdClass->b      = $message;

        $expected = 'rendered-content';

        $output = $this->getMockBuilder(BufferedOutput::class)
            ->disableOriginalConstructor()
            ->getMock();
        $output->expects(self::exactly(2))
            ->method('fetch')
            ->willReturnOnConsecutiveCalls('', $expected);
        $output->expects(self::exactly(5))
            ->method('writeln')
            ->willReturnMap(
                [
                    [str_repeat('=', 220), Output::OUTPUT_NORMAL, null],
                    ['', Output::OUTPUT_NORMAL, null],
                    [$message, Output::OUTPUT_NORMAL, null],
                ],
            );

        $table = $this->getMockBuilder(Table::class)
            ->disableOriginalConstructor()
            ->getMock();
        $table->expects(self::once())
            ->method('setStyle')
            ->with($tableStyle)
            ->willReturnSelf();
        $table->expects(self::exactly(3))
            ->method('setColumnMaxWidth')
            ->willReturnSelf();
        $table->expects(self::once())
            ->method('setColumnWidths')
            ->with([20, 20, 220])
            ->willReturnSelf();
        $table->expects(self::once())
            ->method('setRows')
            ->with([])
            ->willReturnSelf();
        $table->expects(self::exactly(13))
            ->method('addRow');
        $table->expects(self::once())
            ->method('render');

        $formatter = new StreamFormatter($output, $table, '%message% %context.one% %context.five% %context% %extra.app% %extra.app% %extra%', $tableStyle, null, true);

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

        self::assertSame($expected, $formatted);
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     */
    public function testFormatBatch(): void
    {
        $message    = 'test message';
        $channel    = 'test-channel';
        $tableStyle = StreamFormatter::BOX_STYLE;
        $datetime   = new DateTimeImmutable('now');

        $expected1 = 'rendered-content-1';
        $expected2 = 'rendered-content-2';
        $expected3 = 'rendered-content-3';

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

        $output = $this->getMockBuilder(BufferedOutput::class)
            ->disableOriginalConstructor()
            ->getMock();
        $output->expects(self::exactly(6))
            ->method('fetch')
            ->willReturnOnConsecutiveCalls('', $expected1, '', $expected2, '', $expected3);
        $output->expects(self::exactly(15))
            ->method('writeln')
            ->willReturnMap(
                [
                    [str_repeat('=', 220), Output::OUTPUT_NORMAL, null],
                    ['', Output::OUTPUT_NORMAL, null],
                    [$message, Output::OUTPUT_NORMAL, null],
                ],
            );

        $table = $this->getMockBuilder(Table::class)
            ->disableOriginalConstructor()
            ->getMock();
        $table->expects(self::once())
            ->method('setStyle')
            ->with($tableStyle)
            ->willReturnSelf();
        $table->expects(self::exactly(3))
            ->method('setColumnMaxWidth')
            ->willReturnSelf();
        $table->expects(self::once())
            ->method('setColumnWidths')
            ->with([20, 20, 220])
            ->willReturnSelf();
        $table->expects(self::exactly(3))
            ->method('setRows')
            ->with([])
            ->willReturnSelf();
        $table->expects(self::exactly(34))
            ->method('addRow');
        $table->expects(self::exactly(3))
            ->method('render');

        $formatter = new StreamFormatter($output, $table);

        $formatted = $formatter->formatBatch([$record1, $record2, $record3]);

        self::assertSame($expected1 . $expected2 . $expected3, $formatted);
    }
}
