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
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use UnexpectedValueException;

use function assert;
use function file_put_contents;
use function in_array;
use function str_repeat;
use function str_replace;

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
            ->with(
                [StreamFormatter::WIDTH_FIRST_COLUMN, StreamFormatter::WIDTH_SECOND_COLUMN, StreamFormatter::WIDTH_THIRD_COLUMN],
            )
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
            ->with(
                [StreamFormatter::WIDTH_FIRST_COLUMN, StreamFormatter::WIDTH_SECOND_COLUMN, StreamFormatter::WIDTH_THIRD_COLUMN],
            )
            ->willReturnSelf();
        $table->expects(self::never())
            ->method('setRows');
        $table->expects(self::never())
            ->method('addRow');
        $table->expects(self::never())
            ->method('render');

        $formatter = new StreamFormatter(
            $output,
            $table,
            $format,
            $tableStyle,
            $dateFormat,
            true,
            false,
        );

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
            ->with(
                [StreamFormatter::WIDTH_FIRST_COLUMN, StreamFormatter::WIDTH_SECOND_COLUMN, StreamFormatter::WIDTH_THIRD_COLUMN],
            )
            ->willReturnSelf();
        $table->expects(self::never())
            ->method('setRows');
        $table->expects(self::never())
            ->method('addRow');
        $table->expects(self::never())
            ->method('render');

        $formatter = new StreamFormatter(
            $output,
            $table,
            $format,
            $tableStyle,
            $dateFormat,
            false,
            true,
        );

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
            ->with(
                [StreamFormatter::WIDTH_FIRST_COLUMN, StreamFormatter::WIDTH_SECOND_COLUMN, StreamFormatter::WIDTH_THIRD_COLUMN],
            )
            ->willReturnSelf();
        $table->expects(self::never())
            ->method('setRows');
        $table->expects(self::never())
            ->method('addRow');
        $table->expects(self::never())
            ->method('render');

        $formatter = new StreamFormatter(
            $output,
            $table,
            $format,
            $tableStyle,
            $dateFormat,
            false,
            false,
        );

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
            ->with(
                [StreamFormatter::WIDTH_FIRST_COLUMN, StreamFormatter::WIDTH_SECOND_COLUMN, StreamFormatter::WIDTH_THIRD_COLUMN],
            )
            ->willReturnSelf();
        $table->expects(self::never())
            ->method('setRows');
        $table->expects(self::never())
            ->method('addRow');
        $table->expects(self::never())
            ->method('render');

        $formatter = new StreamFormatter(
            $output,
            $table,
            $format,
            $tableStyle,
            $dateFormat,
            true,
            false,
        );

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
        $level    = Level::Error;

        $expected = 'rendered-content';

        $output = $this->getMockBuilder(BufferedOutput::class)
            ->disableOriginalConstructor()
            ->getMock();
        $output->expects(self::exactly(2))
            ->method('fetch')
            ->willReturnOnConsecutiveCalls('', $expected);
        $matcher = self::exactly(5);
        $output->expects($matcher)
            ->method('writeln')
            ->willReturnCallback(
                /** @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter */
                static function (string | iterable $messages, int $options = OutputInterface::OUTPUT_NORMAL) use ($matcher, $message): void {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame(str_repeat('=', StreamFormatter::FULL_WIDTH), $messages),
                        2, 4, 5 => self::assertSame('', $messages),
                        default => self::assertSame($message, $messages),
                    };
                },
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
            ->with(
                [StreamFormatter::WIDTH_FIRST_COLUMN, StreamFormatter::WIDTH_SECOND_COLUMN, StreamFormatter::WIDTH_THIRD_COLUMN],
            )
            ->willReturnSelf();
        $table->expects(self::once())
            ->method('setRows')
            ->with([])
            ->willReturnSelf();
        $matcher = self::exactly(3);
        $table->expects($matcher)
            ->method('addRow')
            ->willReturnCallback(
                static function (TableSeparator | array $row) use ($matcher, $table, $datetime, $level): Table {
                    self::assertIsArray($row, (string) $matcher->numberOfInvocations());

                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertCount(1, $row, (string) $matcher->numberOfInvocations()),
                        default => self::assertCount(2, $row, (string) $matcher->numberOfInvocations()),
                    };

                    if ($matcher->numberOfInvocations() === 1) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell);
                        self::assertSame('General Info', (string) $tableCell);

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 2) {
                        $tableCell1 = $row[0];
                        assert($tableCell1 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell1);
                        self::assertSame('Time', (string) $tableCell1);

                        $tableCell2 = $row[1];
                        assert($tableCell2 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell2);
                        self::assertSame(
                            $datetime->format(NormalizerFormatter::SIMPLE_DATE),
                            (string) $tableCell2,
                        );

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 3) {
                        $tableCell1 = $row[0];
                        assert($tableCell1 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell1);
                        self::assertSame('Level', (string) $tableCell1);

                        $tableCell2 = $row[1];
                        assert($tableCell2 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell2);
                        self::assertSame($level->getName(), (string) $tableCell2);
                    }

                    return $table;
                },
            );
        $table->expects(self::once())
            ->method('render');

        $formatter = new StreamFormatter($output, $table);

        $record = new LogRecord(
            datetime: $datetime,
            channel: $channel,
            level: $level,
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
        $level    = Level::Error;

        $expected = 'rendered-content';

        $output = $this->getMockBuilder(BufferedOutput::class)
            ->disableOriginalConstructor()
            ->getMock();
        $output->expects(self::exactly(2))
            ->method('fetch')
            ->willReturnOnConsecutiveCalls('', $expected);
        $matcher = self::exactly(5);
        $output->expects($matcher)
            ->method('writeln')
            ->willReturnCallback(
                /** @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter */
                static function (string | iterable $messages, int $options = OutputInterface::OUTPUT_NORMAL) use ($matcher, $message): void {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame(str_repeat('=', StreamFormatter::FULL_WIDTH), $messages),
                        2, 4, 5 => self::assertSame('', $messages),
                        default => self::assertSame($message, $messages),
                    };
                },
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
            ->with(
                [StreamFormatter::WIDTH_FIRST_COLUMN, StreamFormatter::WIDTH_SECOND_COLUMN, StreamFormatter::WIDTH_THIRD_COLUMN],
            )
            ->willReturnSelf();
        $table->expects(self::once())
            ->method('setRows')
            ->with([])
            ->willReturnSelf();
        $matcher = self::exactly(15);
        $table->expects($matcher)
            ->method('addRow')
            ->willReturnCallback(
                static function (TableSeparator | array $row) use ($matcher, $table, $datetime, $level): Table {
                    if (in_array($matcher->numberOfInvocations(), [4, 6, 8, 10], true)) {
                        self::assertInstanceOf(
                            TableSeparator::class,
                            $row,
                            (string) $matcher->numberOfInvocations(),
                        );

                        return $table;
                    }

                    self::assertIsArray($row, (string) $matcher->numberOfInvocations());

                    match ($matcher->numberOfInvocations()) {
                        1, 5, 9 => self::assertCount(1, $row, (string) $matcher->numberOfInvocations()),
                        14 => self::assertCount(3, $row, (string) $matcher->numberOfInvocations()),
                        default => self::assertCount(2, $row, (string) $matcher->numberOfInvocations()),
                    };

                    if ($matcher->numberOfInvocations() === 1) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell);
                        self::assertSame('General Info', (string) $tableCell);

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 2) {
                        $tableCell1 = $row[0];
                        assert($tableCell1 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell1);
                        self::assertSame('Time', (string) $tableCell1);

                        $tableCell2 = $row[1];
                        assert($tableCell2 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell2);
                        self::assertSame(
                            $datetime->format(NormalizerFormatter::SIMPLE_DATE),
                            (string) $tableCell2,
                        );

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 3) {
                        $tableCell1 = $row[0];
                        assert($tableCell1 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell1);
                        self::assertSame('Level', (string) $tableCell1);

                        $tableCell2 = $row[1];
                        assert($tableCell2 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell2);
                        self::assertSame($level->getName(), (string) $tableCell2);

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 5) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell);
                        self::assertSame('Extra', (string) $tableCell);

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 9) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell);
                        self::assertSame('Context', (string) $tableCell);
                    }

                    return $table;
                },
            );
        $table->expects(self::once())
            ->method('render');

        $formatter = new StreamFormatter($output, $table);

        $record = new LogRecord(
            datetime: $datetime,
            channel: $channel,
            level: $level,
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
        $level    = Level::Error;

        $expected = 'rendered-content';

        $output = $this->getMockBuilder(BufferedOutput::class)
            ->disableOriginalConstructor()
            ->getMock();
        $output->expects(self::exactly(2))
            ->method('fetch')
            ->willReturnOnConsecutiveCalls('', $expected);
        $matcher = self::exactly(5);
        $output->expects($matcher)
            ->method('writeln')
            ->willReturnCallback(
                /** @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter */
                static function (string | iterable $messages, int $options = OutputInterface::OUTPUT_NORMAL) use ($matcher): void {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame(str_repeat('=', StreamFormatter::FULL_WIDTH), $messages),
                        2, 4, 5 => self::assertSame('', $messages),
                        default => self::assertSame('test message true test-app', $messages),
                    };
                },
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
            ->with(
                [StreamFormatter::WIDTH_FIRST_COLUMN, StreamFormatter::WIDTH_SECOND_COLUMN, StreamFormatter::WIDTH_THIRD_COLUMN],
            )
            ->willReturnSelf();
        $table->expects(self::once())
            ->method('setRows')
            ->with([])
            ->willReturnSelf();
        $matcher = self::exactly(15);
        $table->expects($matcher)
            ->method('addRow')
            ->willReturnCallback(
                static function (TableSeparator | array $row) use ($matcher, $table, $datetime, $level): Table {
                    if (in_array($matcher->numberOfInvocations(), [4, 6, 8, 10], true)) {
                        self::assertInstanceOf(
                            TableSeparator::class,
                            $row,
                            (string) $matcher->numberOfInvocations(),
                        );

                        return $table;
                    }

                    self::assertIsArray($row, (string) $matcher->numberOfInvocations());

                    match ($matcher->numberOfInvocations()) {
                        1, 5, 9 => self::assertCount(1, $row, (string) $matcher->numberOfInvocations()),
                        14 => self::assertCount(3, $row, (string) $matcher->numberOfInvocations()),
                        default => self::assertCount(2, $row, (string) $matcher->numberOfInvocations()),
                    };

                    if ($matcher->numberOfInvocations() === 1) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell);
                        self::assertSame('General Info', (string) $tableCell);

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 2) {
                        $tableCell1 = $row[0];
                        assert($tableCell1 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell1);
                        self::assertSame('Time', (string) $tableCell1);

                        $tableCell2 = $row[1];
                        assert($tableCell2 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell2);
                        self::assertSame(
                            $datetime->format(NormalizerFormatter::SIMPLE_DATE),
                            (string) $tableCell2,
                        );

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 3) {
                        $tableCell1 = $row[0];
                        assert($tableCell1 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell1);
                        self::assertSame('Level', (string) $tableCell1);

                        $tableCell2 = $row[1];
                        assert($tableCell2 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell2);
                        self::assertSame($level->getName(), (string) $tableCell2);

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 5) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell);
                        self::assertSame('Extra', (string) $tableCell);

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 9) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell);
                        self::assertSame('Context', (string) $tableCell);
                    }

                    return $table;
                },
            );
        $table->expects(self::once())
            ->method('render');

        $formatter = new StreamFormatter($output, $table, '%message% %context.two% %extra.app%');

        $record = new LogRecord(
            datetime: $datetime,
            channel: $channel,
            level: $level,
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
        $level    = Level::Error;

        $expected = 'rendered-content';

        $output = $this->getMockBuilder(BufferedOutput::class)
            ->disableOriginalConstructor()
            ->getMock();
        $output->expects(self::exactly(2))
            ->method('fetch')
            ->willReturnOnConsecutiveCalls('', $expected);
        $matcher = self::exactly(5);
        $output->expects($matcher)
            ->method('writeln')
            ->willReturnCallback(
                /** @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter */
                static function (string | iterable $messages, int $options = OutputInterface::OUTPUT_NORMAL) use ($matcher): void {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame(str_repeat('=', StreamFormatter::FULL_WIDTH), $messages),
                        2, 4, 5 => self::assertSame('', $messages),
                        default => self::assertSame('test message ["abc","xyz"] test-app', $messages),
                    };
                },
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
            ->with(
                [StreamFormatter::WIDTH_FIRST_COLUMN, StreamFormatter::WIDTH_SECOND_COLUMN, StreamFormatter::WIDTH_THIRD_COLUMN],
            )
            ->willReturnSelf();
        $table->expects(self::once())
            ->method('setRows')
            ->with([])
            ->willReturnSelf();
        $matcher = self::exactly(15);
        $table->expects($matcher)
            ->method('addRow')
            ->willReturnCallback(
                static function (TableSeparator | array $row) use ($matcher, $table, $datetime, $level): Table {
                    if (in_array($matcher->numberOfInvocations(), [4, 6, 8, 10], true)) {
                        self::assertInstanceOf(
                            TableSeparator::class,
                            $row,
                            (string) $matcher->numberOfInvocations(),
                        );

                        return $table;
                    }

                    self::assertIsArray($row, (string) $matcher->numberOfInvocations());

                    match ($matcher->numberOfInvocations()) {
                        1, 5, 9 => self::assertCount(1, $row, (string) $matcher->numberOfInvocations()),
                        14 => self::assertCount(3, $row, (string) $matcher->numberOfInvocations()),
                        default => self::assertCount(2, $row, (string) $matcher->numberOfInvocations()),
                    };

                    if ($matcher->numberOfInvocations() === 1) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell);
                        self::assertSame('General Info', (string) $tableCell);

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 2) {
                        $tableCell1 = $row[0];
                        assert($tableCell1 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell1);
                        self::assertSame('Time', (string) $tableCell1);

                        $tableCell2 = $row[1];
                        assert($tableCell2 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell2);
                        self::assertSame(
                            $datetime->format(NormalizerFormatter::SIMPLE_DATE),
                            (string) $tableCell2,
                        );

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 3) {
                        $tableCell1 = $row[0];
                        assert($tableCell1 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell1);
                        self::assertSame('Level', (string) $tableCell1);

                        $tableCell2 = $row[1];
                        assert($tableCell2 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell2);
                        self::assertSame($level->getName(), (string) $tableCell2);

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 5) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell);
                        self::assertSame('Extra', (string) $tableCell);

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 9) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell);
                        self::assertSame('Context', (string) $tableCell);
                    }

                    return $table;
                },
            );
        $table->expects(self::once())
            ->method('render');

        $formatter = new StreamFormatter($output, $table, '%message% %context.four% %extra.app%');

        $record = new LogRecord(
            datetime: $datetime,
            channel: $channel,
            level: $level,
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
        $level      = Level::Error;

        $expected = 'rendered-content';

        $output = $this->getMockBuilder(BufferedOutput::class)
            ->disableOriginalConstructor()
            ->getMock();
        $output->expects(self::exactly(2))
            ->method('fetch')
            ->willReturnOnConsecutiveCalls('', $expected);
        $matcher = self::exactly(5);
        $output->expects($matcher)
            ->method('writeln')
            ->willReturnCallback(
                /** @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter */
                static function (string | iterable $messages, int $options = OutputInterface::OUTPUT_NORMAL) use ($matcher): void {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame(str_repeat('=', StreamFormatter::FULL_WIDTH), $messages),
                        2, 4, 5 => self::assertSame('', $messages),
                        default => self::assertSame('test message test test test-app', $messages),
                    };
                },
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
            ->with(
                [StreamFormatter::WIDTH_FIRST_COLUMN, StreamFormatter::WIDTH_SECOND_COLUMN, StreamFormatter::WIDTH_THIRD_COLUMN],
            )
            ->willReturnSelf();
        $table->expects(self::once())
            ->method('setRows')
            ->with([])
            ->willReturnSelf();
        $matcher = self::exactly(16);
        $table->expects($matcher)
            ->method('addRow')
            ->willReturnCallback(
                static function (TableSeparator | array $row) use ($matcher, $table, $datetime, $level): Table {
                    if (in_array($matcher->numberOfInvocations(), [4, 6, 8, 10], true)) {
                        self::assertInstanceOf(
                            TableSeparator::class,
                            $row,
                            (string) $matcher->numberOfInvocations(),
                        );

                        return $table;
                    }

                    self::assertIsArray($row, (string) $matcher->numberOfInvocations());

                    match ($matcher->numberOfInvocations()) {
                        1, 5, 9 => self::assertCount(1, $row, (string) $matcher->numberOfInvocations()),
                        14 => self::assertCount(3, $row, (string) $matcher->numberOfInvocations()),
                        default => self::assertCount(2, $row, (string) $matcher->numberOfInvocations()),
                    };

                    if ($matcher->numberOfInvocations() === 1) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell);
                        self::assertSame('General Info', (string) $tableCell);

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 2) {
                        $tableCell1 = $row[0];
                        assert($tableCell1 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell1);
                        self::assertSame('Time', (string) $tableCell1);

                        $tableCell2 = $row[1];
                        assert($tableCell2 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell2);
                        self::assertSame(
                            $datetime->format(NormalizerFormatter::SIMPLE_DATE),
                            (string) $tableCell2,
                        );

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 3) {
                        $tableCell1 = $row[0];
                        assert($tableCell1 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell1);
                        self::assertSame('Level', (string) $tableCell1);

                        $tableCell2 = $row[1];
                        assert($tableCell2 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell2);
                        self::assertSame($level->getName(), (string) $tableCell2);

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 5) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell);
                        self::assertSame('Extra', (string) $tableCell);

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 9) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell);
                        self::assertSame('Context', (string) $tableCell);
                    }

                    return $table;
                },
            );
        $table->expects(self::once())
            ->method('render');

        $formatter = new StreamFormatter(
            $output,
            $table,
            '%message% %context.five% %extra.app%',
            $tableStyle,
            null,
            false,
        );

        $record = new LogRecord(
            datetime: $datetime,
            channel: $channel,
            level: $level,
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
        $level      = Level::Error;

        $expected = 'rendered-content';

        $output = $this->getMockBuilder(BufferedOutput::class)
            ->disableOriginalConstructor()
            ->getMock();
        $output->expects(self::exactly(2))
            ->method('fetch')
            ->willReturnOnConsecutiveCalls('', $expected);
        $matcher = self::exactly(5);
        $output->expects($matcher)
            ->method('writeln')
            ->willReturnCallback(
                /** @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter */
                static function (string | iterable $messages, int $options = OutputInterface::OUTPUT_NORMAL) use ($matcher): void {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame(str_repeat('=', StreamFormatter::FULL_WIDTH), $messages),
                        2, 4, 5 => self::assertSame('', $messages),
                        default => self::assertSame("test message test\ntest test-app", $messages),
                    };
                },
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
            ->with(
                [StreamFormatter::WIDTH_FIRST_COLUMN, StreamFormatter::WIDTH_SECOND_COLUMN, StreamFormatter::WIDTH_THIRD_COLUMN],
            )
            ->willReturnSelf();
        $table->expects(self::once())
            ->method('setRows')
            ->with([])
            ->willReturnSelf();
        $matcher = self::exactly(16);
        $table->expects($matcher)
            ->method('addRow')
            ->willReturnCallback(
                static function (TableSeparator | array $row) use ($matcher, $table, $datetime, $level): Table {
                    if (in_array($matcher->numberOfInvocations(), [4, 6, 8, 10], true)) {
                        self::assertInstanceOf(
                            TableSeparator::class,
                            $row,
                            (string) $matcher->numberOfInvocations(),
                        );

                        return $table;
                    }

                    self::assertIsArray($row, (string) $matcher->numberOfInvocations());

                    match ($matcher->numberOfInvocations()) {
                        1, 5, 9 => self::assertCount(1, $row, (string) $matcher->numberOfInvocations()),
                        14 => self::assertCount(3, $row, (string) $matcher->numberOfInvocations()),
                        default => self::assertCount(2, $row, (string) $matcher->numberOfInvocations()),
                    };

                    if ($matcher->numberOfInvocations() === 1) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell);
                        self::assertSame('General Info', (string) $tableCell);

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 2) {
                        $tableCell1 = $row[0];
                        assert($tableCell1 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell1);
                        self::assertSame('Time', (string) $tableCell1);

                        $tableCell2 = $row[1];
                        assert($tableCell2 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell2);
                        self::assertSame(
                            $datetime->format(NormalizerFormatter::SIMPLE_DATE),
                            (string) $tableCell2,
                        );

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 3) {
                        $tableCell1 = $row[0];
                        assert($tableCell1 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell1);
                        self::assertSame('Level', (string) $tableCell1);

                        $tableCell2 = $row[1];
                        assert($tableCell2 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell2);
                        self::assertSame($level->getName(), (string) $tableCell2);

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 5) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell);
                        self::assertSame('Extra', (string) $tableCell);

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 9) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell);
                        self::assertSame('Context', (string) $tableCell);
                    }

                    return $table;
                },
            );
        $table->expects(self::once())
            ->method('render');

        $formatter = new StreamFormatter(
            $output,
            $table,
            '%message% %context.five% %extra.app%',
            $tableStyle,
            null,
            true,
        );

        $record = new LogRecord(
            datetime: $datetime,
            channel: $channel,
            level: $level,
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
        $level      = Level::Error;

        $expected = 'rendered-content';

        $output = $this->getMockBuilder(BufferedOutput::class)
            ->disableOriginalConstructor()
            ->getMock();
        $output->expects(self::exactly(2))
            ->method('fetch')
            ->willReturnOnConsecutiveCalls('', $expected);
        $matcher = self::exactly(5);
        $output->expects($matcher)
            ->method('writeln')
            ->willReturnCallback(
                /** @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter */
                static function (string | iterable $messages, int $options = OutputInterface::OUTPUT_NORMAL) use ($matcher, $exception): void {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame(str_repeat('=', StreamFormatter::FULL_WIDTH), $messages),
                        2, 4, 5 => self::assertSame('', $messages),
                        default => self::assertSame(
                            "test message test\ntest <[object] (RuntimeException(code: " . $exception->getCode() . '): error at ' . $exception->getFile() . ':' . $exception->getLine() . ')>',
                            $messages,
                        ),
                    };
                },
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
            ->with(
                [StreamFormatter::WIDTH_FIRST_COLUMN, StreamFormatter::WIDTH_SECOND_COLUMN, StreamFormatter::WIDTH_THIRD_COLUMN],
            )
            ->willReturnSelf();
        $table->expects(self::once())
            ->method('setRows')
            ->with([])
            ->willReturnSelf();
        $matcher = self::exactly(22);
        $table->expects($matcher)
            ->method('addRow')
            ->willReturnCallback(
                static function (TableSeparator | array $row) use ($matcher, $table, $datetime, $level): Table {
                    if (in_array($matcher->numberOfInvocations(), [4, 6, 14, 16], true)) {
                        self::assertInstanceOf(
                            TableSeparator::class,
                            $row,
                            (string) $matcher->numberOfInvocations(),
                        );

                        return $table;
                    }

                    self::assertIsArray($row, (string) $matcher->numberOfInvocations());

                    match ($matcher->numberOfInvocations()) {
                        1, 5, 15 => self::assertCount(
                            1,
                            $row,
                            (string) $matcher->numberOfInvocations(),
                        ),
                        8, 20 => self::assertCount(3, $row, (string) $matcher->numberOfInvocations()),
                        default => self::assertCount(2, $row, (string) $matcher->numberOfInvocations()),
                    };

                    if ($matcher->numberOfInvocations() === 1) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell);
                        self::assertSame('General Info', (string) $tableCell);

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 2) {
                        $tableCell1 = $row[0];
                        assert($tableCell1 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell1);
                        self::assertSame('Time', (string) $tableCell1);

                        $tableCell2 = $row[1];
                        assert($tableCell2 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell2);
                        self::assertSame(
                            $datetime->format(NormalizerFormatter::SIMPLE_DATE),
                            (string) $tableCell2,
                        );

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 3) {
                        $tableCell1 = $row[0];
                        assert($tableCell1 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell1);
                        self::assertSame('Level', (string) $tableCell1);

                        $tableCell2 = $row[1];
                        assert($tableCell2 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell2);
                        self::assertSame($level->getName(), (string) $tableCell2);

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 5) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell);
                        self::assertSame('Extra', (string) $tableCell);

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 8) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell);
                        self::assertSame('Throwable', (string) $tableCell);

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 9) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell);
                        self::assertSame('File', (string) $tableCell);

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 10) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell);
                        self::assertSame('Line', (string) $tableCell);

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 11) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell);
                        self::assertSame('Message', (string) $tableCell);

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 12) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell);
                        self::assertSame('Trace', (string) $tableCell);

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 13) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell);
                        self::assertSame('Type', (string) $tableCell);

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 15) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell);
                        self::assertSame('Context', (string) $tableCell);
                    }

                    if ($matcher->numberOfInvocations() === 20) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell);
                        self::assertSame('four', (string) $tableCell);

                        return $table;
                    }

                    return $table;
                },
            );
        $table->expects(self::once())
            ->method('render');

        $formatter = new StreamFormatter(
            $output,
            $table,
            '%message% %context.five% <%extra.Exception%>',
            $tableStyle,
            null,
            true,
        );

        $record = new LogRecord(
            datetime: $datetime,
            channel: $channel,
            level: $level,
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
        $exception  = new RuntimeException('error');
        $level      = Level::Error;

        $expected = 'rendered-content';

        $output = $this->getMockBuilder(BufferedOutput::class)
            ->disableOriginalConstructor()
            ->getMock();
        $output->expects(self::exactly(2))
            ->method('fetch')
            ->willReturnOnConsecutiveCalls('', $expected);
        $matcher = self::exactly(5);
        $output->expects($matcher)
            ->method('writeln')
            ->willReturnCallback(
                /** @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter */
                static function (string | iterable $messages, int $options = OutputInterface::OUTPUT_NORMAL) use ($matcher): void {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame(str_repeat('=', StreamFormatter::FULL_WIDTH), $messages),
                        2, 4, 5 => self::assertSame('', $messages),
                        default => self::assertSame("test message test\ntest test-app", $messages),
                    };
                },
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
            ->with(
                [StreamFormatter::WIDTH_FIRST_COLUMN, StreamFormatter::WIDTH_SECOND_COLUMN, StreamFormatter::WIDTH_THIRD_COLUMN],
            )
            ->willReturnSelf();
        $table->expects(self::once())
            ->method('setRows')
            ->with([])
            ->willReturnSelf();
        $matcher = self::exactly(22);
        $table->expects($matcher)
            ->method('addRow')
            ->willReturnCallback(
                static function (TableSeparator | array $row) use ($matcher, $table, $datetime, $level): Table {
                    if (in_array($matcher->numberOfInvocations(), [4, 6, 14, 16], true)) {
                        self::assertInstanceOf(
                            TableSeparator::class,
                            $row,
                            (string) $matcher->numberOfInvocations(),
                        );

                        return $table;
                    }

                    self::assertIsArray($row, (string) $matcher->numberOfInvocations());

                    match ($matcher->numberOfInvocations()) {
                        1, 5, 15 => self::assertCount(
                            1,
                            $row,
                            (string) $matcher->numberOfInvocations(),
                        ),
                        8, 20 => self::assertCount(3, $row, (string) $matcher->numberOfInvocations()),
                        default => self::assertCount(2, $row, (string) $matcher->numberOfInvocations()),
                    };

                    if ($matcher->numberOfInvocations() === 1) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell);
                        self::assertSame('General Info', (string) $tableCell);

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 2) {
                        $tableCell1 = $row[0];
                        assert($tableCell1 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell1);
                        self::assertSame('Time', (string) $tableCell1);

                        $tableCell2 = $row[1];
                        assert($tableCell2 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell2);
                        self::assertSame(
                            $datetime->format(NormalizerFormatter::SIMPLE_DATE),
                            (string) $tableCell2,
                        );

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 3) {
                        $tableCell1 = $row[0];
                        assert($tableCell1 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell1);
                        self::assertSame('Level', (string) $tableCell1);

                        $tableCell2 = $row[1];
                        assert($tableCell2 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell2);
                        self::assertSame($level->getName(), (string) $tableCell2);

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 5) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell);
                        self::assertSame('Extra', (string) $tableCell);

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 8) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell);
                        self::assertSame('Throwable', (string) $tableCell);

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 9) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell);
                        self::assertSame('File', (string) $tableCell);

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 10) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell);
                        self::assertSame('Line', (string) $tableCell);

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 11) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell);
                        self::assertSame('Message', (string) $tableCell);

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 12) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell);
                        self::assertSame('Trace', (string) $tableCell);

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 13) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell);
                        self::assertSame('Type', (string) $tableCell);

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 15) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell);
                        self::assertSame('Context', (string) $tableCell);
                    }

                    return $table;
                },
            );
        $table->expects(self::once())
            ->method('render');

        $formatter = new StreamFormatter(
            $output,
            $table,
            '%message% %context.five% %extra.app%',
            $tableStyle,
            null,
            true,
        );

        $record = new LogRecord(
            datetime: $datetime,
            channel: $channel,
            level: $level,
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
        $level      = Level::Error;

        $expected = 'rendered-content';

        $exception1 = new RuntimeException('error');
        $exception2 = new UnexpectedValueException('error', 4711, $exception1);
        $exception3 = new OutOfRangeException('error', 1234, $exception2);

        $output = $this->getMockBuilder(BufferedOutput::class)
            ->disableOriginalConstructor()
            ->getMock();
        $output->expects(self::exactly(2))
            ->method('fetch')
            ->willReturnOnConsecutiveCalls('', $expected);
        $matcher = self::exactly(5);
        $output->expects($matcher)
            ->method('writeln')
            ->willReturnCallback(
                /** @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter */
                static function (string | iterable $messages, int $options = OutputInterface::OUTPUT_NORMAL) use ($matcher): void {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame(str_repeat('=', StreamFormatter::FULL_WIDTH), $messages),
                        2, 4, 5 => self::assertSame('', $messages),
                        default => self::assertSame("test message test\ntest test-app", $messages),
                    };
                },
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
            ->with(
                [StreamFormatter::WIDTH_FIRST_COLUMN, StreamFormatter::WIDTH_SECOND_COLUMN, StreamFormatter::WIDTH_THIRD_COLUMN],
            )
            ->willReturnSelf();
        $table->expects(self::once())
            ->method('setRows')
            ->with([])
            ->willReturnSelf();
        $matcher = self::exactly(34);
        $table->expects($matcher)
            ->method('addRow')
            ->willReturnCallback(
                static function (TableSeparator | array $row) use ($matcher, $table, $datetime, $level): Table {
                    if (in_array($matcher->numberOfInvocations(), [4, 6, 26, 28], true)) {
                        self::assertInstanceOf(
                            TableSeparator::class,
                            $row,
                            (string) $matcher->numberOfInvocations(),
                        );

                        return $table;
                    }

                    self::assertIsArray($row, (string) $matcher->numberOfInvocations());

                    match ($matcher->numberOfInvocations()) {
                        1, 5, 27 => self::assertCount(
                            1,
                            $row,
                            (string) $matcher->numberOfInvocations(),
                        ),
                        8, 14, 20, 32 => self::assertCount(
                            3,
                            $row,
                            (string) $matcher->numberOfInvocations(),
                        ),
                        default => self::assertCount(2, $row, (string) $matcher->numberOfInvocations()),
                    };

                    if ($matcher->numberOfInvocations() === 1) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell);
                        self::assertSame('General Info', (string) $tableCell);

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 2) {
                        $tableCell1 = $row[0];
                        assert($tableCell1 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell1);
                        self::assertSame('Time', (string) $tableCell1);

                        $tableCell2 = $row[1];
                        assert($tableCell2 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell2);
                        self::assertSame(
                            $datetime->format(NormalizerFormatter::SIMPLE_DATE),
                            (string) $tableCell2,
                        );

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 3) {
                        $tableCell1 = $row[0];
                        assert($tableCell1 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell1);
                        self::assertSame('Level', (string) $tableCell1);

                        $tableCell2 = $row[1];
                        assert($tableCell2 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell2);
                        self::assertSame($level->getName(), (string) $tableCell2);

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 5) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell);
                        self::assertSame('Extra', (string) $tableCell);

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 8) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell);
                        self::assertSame('Throwable', (string) $tableCell);

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 9) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell);
                        self::assertSame('File', (string) $tableCell);

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 10) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell);
                        self::assertSame('Line', (string) $tableCell);

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 11) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell);
                        self::assertSame('Message', (string) $tableCell);

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 12) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell);
                        self::assertSame('Trace', (string) $tableCell);

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 13) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell);
                        self::assertSame('Type', (string) $tableCell);

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 14) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell);
                        self::assertSame('previous Throwable', (string) $tableCell);

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 15) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell);
                        self::assertSame('File', (string) $tableCell);

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 16) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell);
                        self::assertSame('Line', (string) $tableCell);

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 17) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell);
                        self::assertSame('Message', (string) $tableCell);

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 18) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell);
                        self::assertSame('Trace', (string) $tableCell);

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 19) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell);
                        self::assertSame('Type', (string) $tableCell);

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 20) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell);
                        self::assertSame('previous Throwable', (string) $tableCell);

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 21) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell);
                        self::assertSame('File', (string) $tableCell);

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 22) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell);
                        self::assertSame('Line', (string) $tableCell);

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 23) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell);
                        self::assertSame('Message', (string) $tableCell);

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 24) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell);
                        self::assertSame('Trace', (string) $tableCell);

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 25) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell);
                        self::assertSame('Type', (string) $tableCell);

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 27) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell);
                        self::assertSame('Context', (string) $tableCell);
                    }

                    return $table;
                },
            );
        $table->expects(self::once())
            ->method('render');

        $formatter = new StreamFormatter(
            $output,
            $table,
            '%message% %context.five% %extra.app%',
            $tableStyle,
            null,
            true,
        );

        $record = new LogRecord(
            datetime: $datetime,
            channel: $channel,
            level: $level,
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
        $level      = Level::Error;

        $exception1 = new RuntimeException('error');
        $exception2 = new UnexpectedValueException('error', 4711, $exception1);
        $exception3 = new OutOfRangeException('error', 1234, $exception2);

        $expected = 'rendered-content';

        $output = $this->getMockBuilder(BufferedOutput::class)
            ->disableOriginalConstructor()
            ->getMock();
        $output->expects(self::exactly(2))
            ->method('fetch')
            ->willReturnOnConsecutiveCalls('', $expected);
        $matcher = self::exactly(5);
        $output->expects($matcher)
            ->method('writeln')
            ->willReturnCallback(
                /** @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter */
                static function (string | iterable $messages, int $options = OutputInterface::OUTPUT_NORMAL) use ($matcher): void {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame(str_repeat('=', StreamFormatter::FULL_WIDTH), $messages),
                        2, 4, 5 => self::assertSame('', $messages),
                        default => self::assertSame(
                            "test message context.one test\ntest test-app extra.Exception",
                            $messages,
                        ),
                    };
                },
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
            ->with(
                [StreamFormatter::WIDTH_FIRST_COLUMN, StreamFormatter::WIDTH_SECOND_COLUMN, StreamFormatter::WIDTH_THIRD_COLUMN],
            )
            ->willReturnSelf();
        $table->expects(self::once())
            ->method('setRows')
            ->with([])
            ->willReturnSelf();
        $matcher = self::exactly(34);
        $table->expects($matcher)
            ->method('addRow')
            ->willReturnCallback(
                static function (TableSeparator | array $row) use ($matcher, $table, $datetime, $level): Table {
                    if (in_array($matcher->numberOfInvocations(), [4, 6, 26, 28], true)) {
                        self::assertInstanceOf(
                            TableSeparator::class,
                            $row,
                            (string) $matcher->numberOfInvocations(),
                        );

                        return $table;
                    }

                    self::assertIsArray($row, (string) $matcher->numberOfInvocations());

                    match ($matcher->numberOfInvocations()) {
                        1, 5, 27 => self::assertCount(
                            1,
                            $row,
                            (string) $matcher->numberOfInvocations(),
                        ),
                        8, 14, 20, 32 => self::assertCount(
                            3,
                            $row,
                            (string) $matcher->numberOfInvocations(),
                        ),
                        default => self::assertCount(2, $row, (string) $matcher->numberOfInvocations()),
                    };

                    if ($matcher->numberOfInvocations() === 1) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell);
                        self::assertSame('General Info', (string) $tableCell);

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 2) {
                        $tableCell1 = $row[0];
                        assert($tableCell1 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell1);
                        self::assertSame('Time', (string) $tableCell1);

                        $tableCell2 = $row[1];
                        assert($tableCell2 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell2);
                        self::assertSame(
                            $datetime->format(NormalizerFormatter::SIMPLE_DATE),
                            (string) $tableCell2,
                        );

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 3) {
                        $tableCell1 = $row[0];
                        assert($tableCell1 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell1);
                        self::assertSame('Level', (string) $tableCell1);

                        $tableCell2 = $row[1];
                        assert($tableCell2 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell2);
                        self::assertSame($level->getName(), (string) $tableCell2);

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 5) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell);
                        self::assertSame('Extra', (string) $tableCell);

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 8) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell);
                        self::assertSame('Throwable', (string) $tableCell);

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 9) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell);
                        self::assertSame('File', (string) $tableCell);

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 10) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell);
                        self::assertSame('Line', (string) $tableCell);

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 11) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell);
                        self::assertSame('Message', (string) $tableCell);

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 12) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell);
                        self::assertSame('Trace', (string) $tableCell);

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 13) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell);
                        self::assertSame('Type', (string) $tableCell);

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 14) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell);
                        self::assertSame('previous Throwable', (string) $tableCell);

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 15) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell);
                        self::assertSame('File', (string) $tableCell);

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 16) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell);
                        self::assertSame('Line', (string) $tableCell);

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 17) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell);
                        self::assertSame('Message', (string) $tableCell);

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 18) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell);
                        self::assertSame('Trace', (string) $tableCell);

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 19) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell);
                        self::assertSame('Type', (string) $tableCell);

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 20) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell);
                        self::assertSame('previous Throwable', (string) $tableCell);

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 21) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell);
                        self::assertSame('File', (string) $tableCell);

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 22) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell);
                        self::assertSame('Line', (string) $tableCell);

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 23) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell);
                        self::assertSame('Message', (string) $tableCell);

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 24) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell);
                        self::assertSame('Trace', (string) $tableCell);

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 25) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell);
                        self::assertSame('Type', (string) $tableCell);

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 27) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell);
                        self::assertSame('Context', (string) $tableCell);
                    }

                    return $table;
                },
            );
        $table->expects(self::once())
            ->method('render');

        $formatter = new StreamFormatter(
            $output,
            $table,
            '%message% context.one %context.five% %extra.app% extra.Exception',
            $tableStyle,
            null,
            true,
        );

        $record = new LogRecord(
            datetime: $datetime,
            channel: $channel,
            level: $level,
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
        $level      = Level::Error;

        $expected = 'rendered-content';

        $output = $this->getMockBuilder(BufferedOutput::class)
            ->disableOriginalConstructor()
            ->getMock();
        $output->expects(self::exactly(2))
            ->method('fetch')
            ->willReturnOnConsecutiveCalls('', $expected);
        $matcher = self::exactly(5);
        $output->expects($matcher)
            ->method('writeln')
            ->willReturnCallback(
                /** @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter */
                static function (string | iterable $messages, int $options = OutputInterface::OUTPUT_NORMAL) use ($matcher): void {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame(str_repeat('=', StreamFormatter::FULL_WIDTH), $messages),
                        2, 4, 5 => self::assertSame('', $messages),
                        default => self::assertSame(
                            "test message NULL test\ntest  test-app test-app",
                            $messages,
                        ),
                    };
                },
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
            ->with(
                [StreamFormatter::WIDTH_FIRST_COLUMN, StreamFormatter::WIDTH_SECOND_COLUMN, StreamFormatter::WIDTH_THIRD_COLUMN],
            )
            ->willReturnSelf();
        $table->expects(self::once())
            ->method('setRows')
            ->with([])
            ->willReturnSelf();
        $matcher = self::exactly(12);
        $table->expects($matcher)
            ->method('addRow')
            ->willReturnCallback(
                static function (TableSeparator | array $row) use ($matcher, $table, $datetime, $level): Table {
                    if (in_array($matcher->numberOfInvocations(), [4, 6, 8, 10], true)) {
                        self::assertInstanceOf(
                            TableSeparator::class,
                            $row,
                            (string) $matcher->numberOfInvocations(),
                        );

                        return $table;
                    }

                    self::assertIsArray($row, (string) $matcher->numberOfInvocations());

                    match ($matcher->numberOfInvocations()) {
                        1, 5, 9 => self::assertCount(1, $row, (string) $matcher->numberOfInvocations()),
                        default => self::assertCount(2, $row, (string) $matcher->numberOfInvocations()),
                    };

                    if ($matcher->numberOfInvocations() === 1) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell);
                        self::assertSame('General Info', (string) $tableCell);

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 2) {
                        $tableCell1 = $row[0];
                        assert($tableCell1 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell1);
                        self::assertSame('Time', (string) $tableCell1);

                        $tableCell2 = $row[1];
                        assert($tableCell2 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell2);
                        self::assertSame(
                            $datetime->format(NormalizerFormatter::SIMPLE_DATE),
                            (string) $tableCell2,
                        );

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 3) {
                        $tableCell1 = $row[0];
                        assert($tableCell1 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell1);
                        self::assertSame('Level', (string) $tableCell1);

                        $tableCell2 = $row[1];
                        assert($tableCell2 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell2);
                        self::assertSame($level->getName(), (string) $tableCell2);

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 5) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell);
                        self::assertSame('Extra', (string) $tableCell);

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 9) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell);
                        self::assertSame('Context', (string) $tableCell);
                    }

                    return $table;
                },
            );
        $table->expects(self::once())
            ->method('render');

        $formatter = new StreamFormatter(
            $output,
            $table,
            '%message% %context.one% %context.five% %context% %extra.app% %extra.app% %extra%',
            $tableStyle,
            null,
            true,
        );

        $record = new LogRecord(
            datetime: $datetime,
            channel: $channel,
            level: $level,
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
        $level            = Level::Error;

        $expected = 'rendered-content';

        $output = $this->getMockBuilder(BufferedOutput::class)
            ->disableOriginalConstructor()
            ->getMock();
        $output->expects(self::exactly(2))
            ->method('fetch')
            ->willReturnOnConsecutiveCalls('', $expected);
        $matcher = self::exactly(5);
        $output->expects($matcher)
            ->method('writeln')
            ->willReturnCallback(
                /** @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter */
                static function (string | iterable $messages, int $options = OutputInterface::OUTPUT_NORMAL) use ($matcher, $formattedMessage): void {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame(str_repeat('=', StreamFormatter::FULL_WIDTH), $messages),
                        2, 4, 5 => self::assertSame('', $messages),
                        default => self::assertSame($formattedMessage, $messages),
                    };
                },
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
            ->with(
                [StreamFormatter::WIDTH_FIRST_COLUMN, StreamFormatter::WIDTH_SECOND_COLUMN, StreamFormatter::WIDTH_THIRD_COLUMN],
            )
            ->willReturnSelf();
        $table->expects(self::once())
            ->method('setRows')
            ->with([])
            ->willReturnSelf();
        $matcher = self::exactly(13);
        $table->expects($matcher)
            ->method('addRow')
            ->willReturnCallback(
                static function (TableSeparator | array $row) use ($matcher, $table, $datetime, $level): Table {
                    if (in_array($matcher->numberOfInvocations(), [4, 6, 8, 10], true)) {
                        self::assertInstanceOf(
                            TableSeparator::class,
                            $row,
                            (string) $matcher->numberOfInvocations(),
                        );

                        return $table;
                    }

                    self::assertIsArray($row, (string) $matcher->numberOfInvocations());

                    match ($matcher->numberOfInvocations()) {
                        1, 5, 9 => self::assertCount(1, $row, (string) $matcher->numberOfInvocations()),
                        13 => self::assertCount(3, $row, (string) $matcher->numberOfInvocations()),
                        default => self::assertCount(2, $row, (string) $matcher->numberOfInvocations()),
                    };

                    if ($matcher->numberOfInvocations() === 1) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell);
                        self::assertSame('General Info', (string) $tableCell);

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 2) {
                        $tableCell1 = $row[0];
                        assert($tableCell1 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell1);
                        self::assertSame('Time', (string) $tableCell1);

                        $tableCell2 = $row[1];
                        assert($tableCell2 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell2);
                        self::assertSame(
                            $datetime->format(NormalizerFormatter::SIMPLE_DATE),
                            (string) $tableCell2,
                        );

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 3) {
                        $tableCell1 = $row[0];
                        assert($tableCell1 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell1);
                        self::assertSame('Level', (string) $tableCell1);

                        $tableCell2 = $row[1];
                        assert($tableCell2 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell2);
                        self::assertSame($level->getName(), (string) $tableCell2);

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 5) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell);
                        self::assertSame('Extra', (string) $tableCell);

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 9) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell);
                        self::assertSame('Context', (string) $tableCell);
                    }

                    return $table;
                },
            );
        $table->expects(self::once())
            ->method('render');

        $formatter = new StreamFormatter(
            $output,
            $table,
            '%message% %context.one% %context.five% %context% %extra.app% %extra.app% %extra%',
            $tableStyle,
            null,
            true,
        );

        $record = new LogRecord(
            datetime: $datetime,
            channel: $channel,
            level: $level,
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
    public function testFormat13(): void
    {
        $message          = 'test message';
        $channel          = 'test-channel';
        $tableStyle       = 'default';
        $datetime         = new DateTimeImmutable('now');
        $formattedMessage = 'this is a formatted message';
        $stdClass         = new stdClass();
        $stdClass->a      = $channel;
        $stdClass->b      = $message;
        $level            = Level::Error;

        $expected = '==============================================================================================================================================================================================================================================================================

this is a formatted message

+----------------------+----------------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| General Info                                                                                                                                                                                                                                                               |
|                 Time | ' . $datetime->format(
            NormalizerFormatter::SIMPLE_DATE,
) . '                                                                                                                                                                                                                           |
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

        $output = new BufferedOutput();
        $table  = new Table($output);

        $formatter = new StreamFormatter(
            $output,
            $table,
            '%message% %context.one% %context.five% %context% %extra.app% %extra.app% %extra%',
            $tableStyle,
            null,
            true,
        );

        $record = new LogRecord(
            datetime: $datetime,
            channel: $channel,
            level: $level,
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

        self::assertSame(
            str_replace("\r\n", "\n", $expected),
            str_replace("\r\n", "\n", $formatted),
        );
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     */
    public function testFormat14(): void
    {
        $message          = ' test message ';
        $channel          = 'test-channel';
        $tableStyle       = 'default';
        $datetime         = new DateTimeImmutable('now');
        $formattedMessage = 'this is a formatted message';
        $stdClass         = new stdClass();
        $stdClass->a      = $channel;
        $stdClass->b      = $message;
        $level            = Level::Error;

        $expected = '==============================================================================================================================================================================================================================================================================

this is a formatted message

+----------------------+----------------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| General Info                                                                                                                                                                                                                                                               |
|                 Time | ' . $datetime->format(
            NormalizerFormatter::SIMPLE_DATE,
        ) . '                                                                                                                                                                                                                           |
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
|                  six | stdClass             | {"a":"test-channel","b":" test message "}                                                                                                                                                                                    |
+----------------------+----------------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+

';

        $output = new BufferedOutput();
        $table  = new Table($output);

        $formatter = new StreamFormatter(
            $output,
            $table,
            '%message% %context.one% %context.five% %context% %extra.app% %extra.app% %extra%',
            $tableStyle,
            null,
            true,
        );

        $record = new LogRecord(
            datetime: $datetime,
            channel: $channel,
            level: $level,
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

        self::assertSame(
            str_replace("\r\n", "\n", $expected),
            str_replace("\r\n", "\n", $formatted),
        );
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
        $level1     = Level::Error;
        $level2     = Level::Error;
        $level3     = Level::Error;

        $expected1 = 'rendered-content-1';
        $expected2 = 'rendered-content-2';
        $expected3 = 'rendered-content-3';

        $record1 = new LogRecord(
            datetime: $datetime,
            channel: $channel,
            level: $level1,
            message: $message,
            context: [],
            extra: [],
        );
        $record2 = new LogRecord(
            datetime: $datetime,
            channel: $channel,
            level: $level2,
            message: $message,
            context: ['one' => null, 'two' => true, 'three' => false, 'four' => ['abc', 'xyz']],
            extra: ['app' => 'test-app'],
        );
        $record3 = new LogRecord(
            datetime: $datetime,
            channel: $channel,
            level: $level3,
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
        $matcher = self::exactly(15);
        $output->expects($matcher)
            ->method('writeln')
            ->willReturnCallback(
                /** @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter */
                static function (string | iterable $messages, int $options = OutputInterface::OUTPUT_NORMAL) use ($matcher, $message): void {
                    match ($matcher->numberOfInvocations()) {
                        1, 6, 11 => self::assertSame(
                            str_repeat('=', StreamFormatter::FULL_WIDTH),
                            $messages,
                            (string) $matcher->numberOfInvocations(),
                        ),
                        2, 4, 5, 7, 9, 10, 12, 14, 15 => self::assertSame(
                            '',
                            $messages,
                            (string) $matcher->numberOfInvocations(),
                        ),
                        default => self::assertSame(
                            $message,
                            $messages,
                            (string) $matcher->numberOfInvocations(),
                        ),
                    };
                },
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
            ->with(
                [StreamFormatter::WIDTH_FIRST_COLUMN, StreamFormatter::WIDTH_SECOND_COLUMN, StreamFormatter::WIDTH_THIRD_COLUMN],
            )
            ->willReturnSelf();
        $table->expects(self::exactly(3))
            ->method('setRows')
            ->with([])
            ->willReturnSelf();
        $matcher = self::exactly(34);
        $table->expects($matcher)
            ->method('addRow')
            ->willReturnCallback(
                static function (TableSeparator | array $row) use ($matcher, $table, $datetime, $level1, $level2, $level3): Table {
                    if (
                        in_array(
                            $matcher->numberOfInvocations(),
                            [7, 9, 11, 13, 22, 24, 26, 28],
                            true,
                        )
                    ) {
                        self::assertInstanceOf(
                            TableSeparator::class,
                            $row,
                            (string) $matcher->numberOfInvocations(),
                        );

                        return $table;
                    }

                    self::assertIsArray($row, (string) $matcher->numberOfInvocations());

                    match ($matcher->numberOfInvocations()) {
                        1, 4, 8, 12, 19, 23, 27 => self::assertCount(
                            1,
                            $row,
                            (string) $matcher->numberOfInvocations(),
                        ),
                        17, 32 => self::assertCount(3, $row, (string) $matcher->numberOfInvocations()),
                        default => self::assertCount(2, $row, (string) $matcher->numberOfInvocations()),
                    };

                    if (
                        $matcher->numberOfInvocations() === 1
                        || $matcher->numberOfInvocations() === 4
                        || $matcher->numberOfInvocations() === 19
                    ) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(
                            TableCell::class,
                            $tableCell,
                            (string) $matcher->numberOfInvocations(),
                        );
                        self::assertSame(
                            'General Info',
                            (string) $tableCell,
                            (string) $matcher->numberOfInvocations(),
                        );

                        return $table;
                    }

                    if (
                        $matcher->numberOfInvocations() === 2
                        || $matcher->numberOfInvocations() === 5
                        || $matcher->numberOfInvocations() === 20
                    ) {
                        $tableCell1 = $row[0];
                        assert($tableCell1 instanceof TableCell);

                        self::assertInstanceOf(
                            TableCell::class,
                            $tableCell1,
                            (string) $matcher->numberOfInvocations(),
                        );
                        self::assertSame(
                            'Time',
                            (string) $tableCell1,
                            (string) $matcher->numberOfInvocations(),
                        );

                        $tableCell2 = $row[1];
                        assert($tableCell2 instanceof TableCell);

                        self::assertInstanceOf(
                            TableCell::class,
                            $tableCell2,
                            (string) $matcher->numberOfInvocations(),
                        );
                        self::assertSame(
                            $datetime->format(NormalizerFormatter::SIMPLE_DATE),
                            (string) $tableCell2,
                            (string) $matcher->numberOfInvocations(),
                        );

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 3) {
                        $tableCell1 = $row[0];
                        assert($tableCell1 instanceof TableCell);

                        self::assertInstanceOf(
                            TableCell::class,
                            $tableCell1,
                            (string) $matcher->numberOfInvocations(),
                        );
                        self::assertSame(
                            'Level',
                            (string) $tableCell1,
                            (string) $matcher->numberOfInvocations(),
                        );

                        $tableCell2 = $row[1];
                        assert($tableCell2 instanceof TableCell);

                        self::assertInstanceOf(
                            TableCell::class,
                            $tableCell2,
                            (string) $matcher->numberOfInvocations(),
                        );
                        self::assertSame(
                            $level1->getName(),
                            (string) $tableCell2,
                            (string) $matcher->numberOfInvocations(),
                        );

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 6) {
                        $tableCell1 = $row[0];
                        assert($tableCell1 instanceof TableCell);

                        self::assertInstanceOf(
                            TableCell::class,
                            $tableCell1,
                            (string) $matcher->numberOfInvocations(),
                        );
                        self::assertSame(
                            'Level',
                            (string) $tableCell1,
                            (string) $matcher->numberOfInvocations(),
                        );

                        $tableCell2 = $row[1];
                        assert($tableCell2 instanceof TableCell);

                        self::assertInstanceOf(
                            TableCell::class,
                            $tableCell2,
                            (string) $matcher->numberOfInvocations(),
                        );
                        self::assertSame(
                            $level2->getName(),
                            (string) $tableCell2,
                            (string) $matcher->numberOfInvocations(),
                        );

                        return $table;
                    }

                    if ($matcher->numberOfInvocations() === 21) {
                        $tableCell1 = $row[0];
                        assert($tableCell1 instanceof TableCell);

                        self::assertInstanceOf(
                            TableCell::class,
                            $tableCell1,
                            (string) $matcher->numberOfInvocations(),
                        );
                        self::assertSame(
                            'Level',
                            (string) $tableCell1,
                            (string) $matcher->numberOfInvocations(),
                        );

                        $tableCell2 = $row[1];
                        assert($tableCell2 instanceof TableCell);

                        self::assertInstanceOf(
                            TableCell::class,
                            $tableCell2,
                            (string) $matcher->numberOfInvocations(),
                        );
                        self::assertSame(
                            $level3->getName(),
                            (string) $tableCell2,
                            (string) $matcher->numberOfInvocations(),
                        );

                        return $table;
                    }

                    if (
                        $matcher->numberOfInvocations() === 8
                        || $matcher->numberOfInvocations() === 23
                    ) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(
                            TableCell::class,
                            $tableCell,
                            (string) $matcher->numberOfInvocations(),
                        );
                        self::assertSame(
                            'Extra',
                            (string) $tableCell,
                            (string) $matcher->numberOfInvocations(),
                        );

                        return $table;
                    }

                    if (
                        $matcher->numberOfInvocations() === 12
                        || $matcher->numberOfInvocations() === 27
                    ) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(
                            TableCell::class,
                            $tableCell,
                            (string) $matcher->numberOfInvocations(),
                        );
                        self::assertSame(
                            'Context',
                            (string) $tableCell,
                            (string) $matcher->numberOfInvocations(),
                        );
                    }

                    return $table;
                },
            );
        $table->expects(self::exactly(3))
            ->method('render');

        $formatter = new StreamFormatter($output, $table);

        $formatted = $formatter->formatBatch([$record1, $record2, $record3]);

        self::assertSame($expected1 . $expected2 . $expected3, $formatted);
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     */
    public function testFormatBatch2(): void
    {
        $message    = 'test message';
        $channel    = 'test-channel';
        $tableStyle = StreamFormatter::BOX_STYLE;
        $datetime   = new DateTimeImmutable('now');

        $expected1 = '==============================================================================================================================================================================================================================================================================

test message

├──────────────────────┼──────────────────────┼──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ General Info                                                                                                                                                                                                                                                               │
│                 Time │ ' . $datetime->format(
            NormalizerFormatter::SIMPLE_DATE,
) . '                                                                                                                                                                                                                           │
│                Level │ ERROR                                                                                                                                                                                                                                               │
└──────────────────────┴──────────────────────┴──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┘

';
        $expected2 = '==============================================================================================================================================================================================================================================================================

test message

├──────────────────────┼──────────────────────┼──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ General Info                                                                                                                                                                                                                                                               │
│                 Time │ ' . $datetime->format(
            NormalizerFormatter::SIMPLE_DATE,
) . '                                                                                                                                                                                                                           │
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
        $expected3 = '==============================================================================================================================================================================================================================================================================

test message

├──────────────────────┼──────────────────────┼──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ General Info                                                                                                                                                                                                                                                               │
│                 Time │ ' . $datetime->format(
            NormalizerFormatter::SIMPLE_DATE,
) . '                                                                                                                                                                                                                           │
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

        $output = new BufferedOutput();
        $table  = new Table($output);

        $formatter = new StreamFormatter($output, $table, null, $tableStyle);

        $formatted = $formatter->formatBatch([$record1, $record2, $record3]);

        file_put_contents('output.txt', $formatted);

        self::assertSame(
            str_replace("\r\n", "\n", $expected1 . $expected2 . $expected3),
            str_replace("\r\n", "\n", $formatted),
        );
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     */
    public function testFormatBatch3(): void
    {
        $message    = 'test message';
        $channel    = 'test-channel';
        $tableStyle = StreamFormatter::BOX_STYLE;
        $datetime   = new DateTimeImmutable('now');

        $expected1 = '==============================================================================================================================================================================================================================================================================

test message

├──────────────────────┼──────────────────────┼──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ General Info                                                                                                                                                                                                                                                               │
│                 Time │ ' . $datetime->format(
            NormalizerFormatter::SIMPLE_DATE,
        ) . '                                                                                                                                                                                                                           │
│                Level │ ERROR                                                                                                                                                                                                                                               │
└──────────────────────┴──────────────────────┴──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┘

';
        $expected2 = '==============================================================================================================================================================================================================================================================================

test message

├──────────────────────┼──────────────────────┼──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ General Info                                                                                                                                                                                                                                                               │
│                 Time │ ' . $datetime->format(
            NormalizerFormatter::SIMPLE_DATE,
        ) . '                                                                                                                                                                                                                           │
│                Level │ ERROR                                                                                                                                                                                                                                               │
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
        $expected3 = '==============================================================================================================================================================================================================================================================================

test message

├──────────────────────┼──────────────────────┼──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ General Info                                                                                                                                                                                                                                                               │
│                 Time │ ' . $datetime->format(
            NormalizerFormatter::SIMPLE_DATE,
        ) . '                                                                                                                                                                                                                           │
│                Level │ ERROR                                                                                                                                                                                                                                               │
├──────────────────────┼──────────────────────┼──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ Extra                                                                                                                                                                                                                                                                      │
├──────────────────────┼──────────────────────┼──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│                  app │ test-app                                                                                                                                                                                                                                            │
└──────────────────────┴──────────────────────┴──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┘

';

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
            extra: [],
        );
        $record3 = new LogRecord(
            datetime: $datetime,
            channel: $channel,
            level: Level::Error,
            message: $message,
            context: [],
            extra: ['app' => 'test-app'],
        );

        $output = new BufferedOutput();
        $table  = new Table($output);

        $formatter = new StreamFormatter($output, $table, null, $tableStyle);

        $formatted = $formatter->formatBatch([$record1, $record2, $record3]);

        file_put_contents('output.txt', $formatted);

        self::assertSame(
            str_replace("\r\n", "\n", $expected1 . $expected2 . $expected3),
            str_replace("\r\n", "\n", $formatted),
        );
    }
}
