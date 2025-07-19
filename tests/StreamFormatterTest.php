<?php

/**
 * This file is part of the mimmi20/monolog-streamformatter package.
 *
 * Copyright (c) 2022-2025, Thomas Mueller <mimmi20@live.de>
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
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testConstructWithDefaults(): void
    {
        $output = $this->createMock(BufferedOutput::class);
        $output->expects(self::never())
            ->method('fetch');
        $output->expects(self::never())
            ->method('writeln');

        $table = $this->createMock(Table::class);
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

        $formatter = new StreamFormatter(output: $output, table: $table);

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
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testConstructWithValues(): void
    {
        $format     = '[%level_name%] %message%';
        $tableStyle = 'test-style';
        $dateFormat = 'c';

        $output = $this->createMock(BufferedOutput::class);
        $output->expects(self::never())
            ->method('fetch');
        $output->expects(self::never())
            ->method('writeln');

        $table = $this->createMock(Table::class);
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
            output: $output,
            table: $table,
            format: $format,
            tableStyle: $tableStyle,
            dateFormat: $dateFormat,
            allowInlineLineBreaks: true,
            includeStacktraces: false,
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
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testConstructWithValues2(): void
    {
        $format     = '[%level_name%] %message%';
        $tableStyle = 'test-style';
        $dateFormat = 'c';

        $output = $this->createMock(BufferedOutput::class);
        $output->expects(self::never())
            ->method('fetch');
        $output->expects(self::never())
            ->method('writeln');

        $table = $this->createMock(Table::class);
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
            output: $output,
            table: $table,
            format: $format,
            tableStyle: $tableStyle,
            dateFormat: $dateFormat,
            allowInlineLineBreaks: false,
            includeStacktraces: true,
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
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testConstructWithValues3(): void
    {
        $format     = '[%level_name%] %message%';
        $tableStyle = 'test-style';
        $dateFormat = 'c';

        $output = $this->createMock(BufferedOutput::class);
        $output->expects(self::never())
            ->method('fetch');
        $output->expects(self::never())
            ->method('writeln');

        $table = $this->createMock(Table::class);
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
            output: $output,
            table: $table,
            format: $format,
            tableStyle: $tableStyle,
            dateFormat: $dateFormat,
            allowInlineLineBreaks: false,
            includeStacktraces: false,
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
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testConstructWithValues4(): void
    {
        $format     = '[%level_name%] %message%';
        $tableStyle = 'test-style';
        $dateFormat = 'c';

        $output = $this->createMock(BufferedOutput::class);
        $output->expects(self::never())
            ->method('fetch');
        $output->expects(self::never())
            ->method('writeln');

        $table = $this->createMock(Table::class);
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
            output: $output,
            table: $table,
            format: $format,
            tableStyle: $tableStyle,
            dateFormat: $dateFormat,
            allowInlineLineBreaks: true,
            includeStacktraces: false,
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
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testFormat(): void
    {
        $message  = 'test message';
        $channel  = 'test-channel';
        $datetime = new DateTimeImmutable('now');
        $level    = Level::Error;

        $expected = 'rendered-content';

        $output  = $this->createMock(BufferedOutput::class);
        $matcher = self::exactly(2);
        $output->expects($matcher)
            ->method('fetch')
            ->willReturnCallback(
                static fn (): string => match ($matcher->numberOfInvocations()) {
                    1 => '',
                    default => $expected,
                },
            );
        $matcher = self::exactly(5);
        $output->expects($matcher)
            ->method('writeln')
            ->willReturnCallback(
                /** @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter */
                static function (string | iterable $messages, int $options = OutputInterface::OUTPUT_NORMAL) use ($matcher, $message): void {
                    $invocation = $matcher->numberOfInvocations();

                    match ($invocation) {
                        1 => self::assertSame(
                            str_repeat('=', StreamFormatter::FULL_WIDTH),
                            $messages,
                            (string) $invocation,
                        ),
                        2, 4, 5 => self::assertSame('', $messages, (string) $invocation),
                        default => self::assertSame($message, $messages, (string) $invocation),
                    };
                },
            );

        $table = $this->createMock(Table::class);
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
        $matcher = self::exactly(2);
        $table->expects($matcher)
            ->method('addRow')
            ->willReturnCallback(
                static function (TableSeparator | array $row) use ($matcher, $table, $datetime, $level): Table {
                    $invocation = $matcher->numberOfInvocations();

                    self::assertIsArray($row, (string) $invocation);
                    self::assertCount(2, $row, (string) $invocation);

                    if ($invocation === 1) {
                        $tableCell1 = $row[0];
                        assert($tableCell1 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell1, (string) $invocation);
                        self::assertSame('Time', (string) $tableCell1, (string) $invocation);

                        $tableCell2 = $row[1];
                        assert($tableCell2 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell2, (string) $invocation);
                        self::assertSame(
                            $datetime->format(NormalizerFormatter::SIMPLE_DATE),
                            (string) $tableCell2,
                            (string) $invocation,
                        );

                        return $table;
                    }

                    if ($invocation === 3) {
                        $tableCell1 = $row[0];
                        assert($tableCell1 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell1, (string) $invocation);
                        self::assertSame('Level', (string) $tableCell1, (string) $invocation);

                        $tableCell2 = $row[1];
                        assert($tableCell2 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell2, (string) $invocation);
                        self::assertSame($level->getName(), (string) $tableCell2, (string) $invocation);
                    }

                    return $table;
                },
            );
        $table->expects(self::once())
            ->method('render');

        $formatter = new StreamFormatter(output: $output, table: $table);

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
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testFormat2(): void
    {
        $message  = 'test message';
        $channel  = 'test-channel';
        $datetime = new DateTimeImmutable('now');
        $level    = Level::Error;

        $expected = 'rendered-content';

        $output  = $this->createMock(BufferedOutput::class);
        $matcher = self::exactly(2);
        $output->expects($matcher)
            ->method('fetch')
            ->willReturnCallback(
                static fn (): string => match ($matcher->numberOfInvocations()) {
                    1 => '',
                    default => $expected,
                },
            );
        $matcher = self::exactly(5);
        $output->expects($matcher)
            ->method('writeln')
            ->willReturnCallback(
                /** @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter */
                static function (string | iterable $messages, int $options = OutputInterface::OUTPUT_NORMAL) use ($matcher, $message): void {
                    $invocation = $matcher->numberOfInvocations();

                    match ($invocation) {
                        1 => self::assertSame(
                            str_repeat('=', StreamFormatter::FULL_WIDTH),
                            $messages,
                            (string) $invocation,
                        ),
                        2, 4, 5 => self::assertSame('', $messages, (string) $invocation),
                        default => self::assertSame($message, $messages, (string) $invocation),
                    };
                },
            );

        $table = $this->createMock(Table::class);
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
        $matcher = self::exactly(14);
        $table->expects($matcher)
            ->method('addRow')
            ->willReturnCallback(
                static function (TableSeparator | array $row) use ($matcher, $table, $datetime, $level): Table {
                    $invocation = $matcher->numberOfInvocations();

                    if (in_array($invocation, [3, 5, 7, 9], true)) {
                        self::assertInstanceOf(
                            TableSeparator::class,
                            $row,
                            (string) $invocation,
                        );

                        return $table;
                    }

                    self::assertIsArray($row, (string) $invocation);

                    match ($invocation) {
                        4, 8 => self::assertCount(1, $row, (string) $invocation),
                        13 => self::assertCount(3, $row, (string) $invocation),
                        default => self::assertCount(2, $row, (string) $invocation),
                    };

                    if ($invocation === 1) {
                        $tableCell1 = $row[0];
                        assert($tableCell1 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell1, (string) $invocation);
                        self::assertSame('Time', (string) $tableCell1, (string) $invocation);

                        $tableCell2 = $row[1];
                        assert($tableCell2 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell2, (string) $invocation);
                        self::assertSame(
                            $datetime->format(NormalizerFormatter::SIMPLE_DATE),
                            (string) $tableCell2,
                            (string) $invocation,
                        );

                        return $table;
                    }

                    if ($invocation === 2) {
                        $tableCell1 = $row[0];
                        assert($tableCell1 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell1, (string) $invocation);
                        self::assertSame('Level', (string) $tableCell1, (string) $invocation);

                        $tableCell2 = $row[1];
                        assert($tableCell2 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell2, (string) $invocation);
                        self::assertSame($level->getName(), (string) $tableCell2, (string) $invocation);

                        return $table;
                    }

                    if ($invocation === 4) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell, (string) $invocation);
                        self::assertSame('Extra', (string) $tableCell, (string) $invocation);

                        return $table;
                    }

                    if ($invocation === 8) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell, (string) $invocation);
                        self::assertSame('Context', (string) $tableCell, (string) $invocation);
                    }

                    return $table;
                },
            );
        $table->expects(self::once())
            ->method('render');

        $formatter = new StreamFormatter(output: $output, table: $table);

        $record = new LogRecord(
            datetime: $datetime,
            channel: $channel,
            level: $level,
            message: $message,
            context: ['one' => null, 'two' => true, 0 => 'numeric-key', 'three' => false, 'four' => ['abc', 'xyz']],
            extra: ['app' => 'test-app'],
        );

        $formatted = $formatter->format($record);

        self::assertSame($expected, $formatted);
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testFormat3(): void
    {
        $message  = 'test message';
        $channel  = 'test-channel';
        $datetime = new DateTimeImmutable('now');
        $level    = Level::Error;

        $expected = 'rendered-content';

        $output  = $this->createMock(BufferedOutput::class);
        $matcher = self::exactly(2);
        $output->expects($matcher)
            ->method('fetch')
            ->willReturnCallback(
                static fn (): string => match ($matcher->numberOfInvocations()) {
                    1 => '',
                    default => $expected,
                },
            );
        $matcher = self::exactly(5);
        $output->expects($matcher)
            ->method('writeln')
            ->willReturnCallback(
                /** @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter */
                static function (string | iterable $messages, int $options = OutputInterface::OUTPUT_NORMAL) use ($matcher): void {
                    $invocation = $matcher->numberOfInvocations();

                    match ($invocation) {
                        1 => self::assertSame(
                            str_repeat('=', StreamFormatter::FULL_WIDTH),
                            $messages,
                            (string) $invocation,
                        ),
                        2, 4, 5 => self::assertSame('', $messages, (string) $invocation),
                        default => self::assertSame(
                            'test message true test-app',
                            $messages,
                            (string) $invocation,
                        ),
                    };
                },
            );

        $table = $this->createMock(Table::class);
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
        $matcher = self::exactly(14);
        $table->expects($matcher)
            ->method('addRow')
            ->willReturnCallback(
                static function (TableSeparator | array $row) use ($matcher, $table, $datetime, $level): Table {
                    $invocation = $matcher->numberOfInvocations();

                    if (in_array($invocation, [3, 5, 7, 9], true)) {
                        self::assertInstanceOf(
                            TableSeparator::class,
                            $row,
                            (string) $invocation,
                        );

                        return $table;
                    }

                    self::assertIsArray($row, (string) $invocation);

                    match ($invocation) {
                        4, 8 => self::assertCount(1, $row, (string) $invocation),
                        13 => self::assertCount(3, $row, (string) $invocation),
                        default => self::assertCount(2, $row, (string) $invocation),
                    };

                    if ($invocation === 1) {
                        $tableCell1 = $row[0];
                        assert($tableCell1 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell1, (string) $invocation);
                        self::assertSame('Time', (string) $tableCell1, (string) $invocation);

                        $tableCell2 = $row[1];
                        assert($tableCell2 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell2, (string) $invocation);
                        self::assertSame(
                            $datetime->format(NormalizerFormatter::SIMPLE_DATE),
                            (string) $tableCell2,
                            (string) $invocation,
                        );

                        return $table;
                    }

                    if ($invocation === 2) {
                        $tableCell1 = $row[0];
                        assert($tableCell1 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell1, (string) $invocation);
                        self::assertSame('Level', (string) $tableCell1, (string) $invocation);

                        $tableCell2 = $row[1];
                        assert($tableCell2 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell2, (string) $invocation);
                        self::assertSame($level->getName(), (string) $tableCell2, (string) $invocation);

                        return $table;
                    }

                    if ($invocation === 4) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell, (string) $invocation);
                        self::assertSame('Extra', (string) $tableCell, (string) $invocation);

                        return $table;
                    }

                    if ($invocation === 8) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell, (string) $invocation);
                        self::assertSame('Context', (string) $tableCell, (string) $invocation);
                    }

                    return $table;
                },
            );
        $table->expects(self::once())
            ->method('render');

        $formatter = new StreamFormatter(
            output: $output,
            table: $table,
            format: '%message% %context.two% %extra.app%',
        );

        $record = new LogRecord(
            datetime: $datetime,
            channel: $channel,
            level: $level,
            message: $message,
            context: ['one' => null, 'two' => true, 0 => 'numeric-key', 'three' => false, 'four' => ['abc', 'xyz']],
            extra: ['app' => 'test-app'],
        );

        $formatted = $formatter->format($record);

        self::assertSame($expected, $formatted);
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testFormat4(): void
    {
        $message  = 'test message';
        $channel  = 'test-channel';
        $datetime = new DateTimeImmutable('now');
        $level    = Level::Error;

        $expected = 'rendered-content';

        $output  = $this->createMock(BufferedOutput::class);
        $matcher = self::exactly(2);
        $output->expects($matcher)
            ->method('fetch')
            ->willReturnCallback(
                static fn (): string => match ($matcher->numberOfInvocations()) {
                    1 => '',
                    default => $expected,
                },
            );
        $matcher = self::exactly(5);
        $output->expects($matcher)
            ->method('writeln')
            ->willReturnCallback(
                /** @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter */
                static function (string | iterable $messages, int $options = OutputInterface::OUTPUT_NORMAL) use ($matcher): void {
                    $invocation = $matcher->numberOfInvocations();

                    match ($invocation) {
                        1 => self::assertSame(
                            str_repeat('=', StreamFormatter::FULL_WIDTH),
                            $messages,
                            (string) $invocation,
                        ),
                        2, 4, 5 => self::assertSame('', $messages, (string) $invocation),
                        default => self::assertSame(
                            'test message ["abc","xyz"] test-app',
                            $messages,
                            (string) $invocation,
                        ),
                    };
                },
            );

        $table = $this->createMock(Table::class);
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
        $matcher = self::exactly(14);
        $table->expects($matcher)
            ->method('addRow')
            ->willReturnCallback(
                static function (TableSeparator | array $row) use ($matcher, $table, $datetime, $level): Table {
                    $invocation = $matcher->numberOfInvocations();

                    if (in_array($invocation, [3, 5, 7, 9], true)) {
                        self::assertInstanceOf(
                            TableSeparator::class,
                            $row,
                            (string) $invocation,
                        );

                        return $table;
                    }

                    self::assertIsArray($row, (string) $invocation);

                    match ($invocation) {
                        4, 8 => self::assertCount(1, $row, (string) $invocation),
                        13 => self::assertCount(3, $row, (string) $invocation),
                        default => self::assertCount(2, $row, (string) $invocation),
                    };

                    if ($invocation === 1) {
                        $tableCell1 = $row[0];
                        assert($tableCell1 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell1, (string) $invocation);
                        self::assertSame('Time', (string) $tableCell1, (string) $invocation);

                        $tableCell2 = $row[1];
                        assert($tableCell2 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell2, (string) $invocation);
                        self::assertSame(
                            $datetime->format(NormalizerFormatter::SIMPLE_DATE),
                            (string) $tableCell2,
                            (string) $invocation,
                        );

                        return $table;
                    }

                    if ($invocation === 2) {
                        $tableCell1 = $row[0];
                        assert($tableCell1 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell1, (string) $invocation);
                        self::assertSame('Level', (string) $tableCell1, (string) $invocation);

                        $tableCell2 = $row[1];
                        assert($tableCell2 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell2, (string) $invocation);
                        self::assertSame($level->getName(), (string) $tableCell2, (string) $invocation);

                        return $table;
                    }

                    if ($invocation === 4) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell, (string) $invocation);
                        self::assertSame('Extra', (string) $tableCell, (string) $invocation);

                        return $table;
                    }

                    if ($invocation === 8) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell, (string) $invocation);
                        self::assertSame('Context', (string) $tableCell, (string) $invocation);
                    }

                    return $table;
                },
            );
        $table->expects(self::once())
            ->method('render');

        $formatter = new StreamFormatter(
            output: $output,
            table: $table,
            format: '%message% %context.four% %extra.app%',
        );

        $record = new LogRecord(
            datetime: $datetime,
            channel: $channel,
            level: $level,
            message: $message,
            context: ['one' => null, 'two' => true, 0 => 'numeric-key', 'three' => false, 'four' => ['abc', 'xyz']],
            extra: ['app' => 'test-app'],
        );

        $formatted = $formatter->format($record);

        self::assertSame($expected, $formatted);
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testFormat5(): void
    {
        $message    = 'test message';
        $channel    = 'test-channel';
        $datetime   = new DateTimeImmutable('now');
        $tableStyle = 'default';
        $level      = Level::Error;

        $expected = 'rendered-content';

        $output  = $this->createMock(BufferedOutput::class);
        $matcher = self::exactly(2);
        $output->expects($matcher)
            ->method('fetch')
            ->willReturnCallback(
                static fn (): string => match ($matcher->numberOfInvocations()) {
                    1 => '',
                    default => $expected,
                },
            );
        $matcher = self::exactly(5);
        $output->expects($matcher)
            ->method('writeln')
            ->willReturnCallback(
                /** @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter */
                static function (string | iterable $messages, int $options = OutputInterface::OUTPUT_NORMAL) use ($matcher): void {
                    $invocation = $matcher->numberOfInvocations();

                    match ($invocation) {
                        1 => self::assertSame(
                            str_repeat('=', StreamFormatter::FULL_WIDTH),
                            $messages,
                            (string) $invocation,
                        ),
                        2, 4, 5 => self::assertSame('', $messages, (string) $invocation),
                        default => self::assertSame(
                            'test message test test test-app',
                            $messages,
                            (string) $invocation,
                        ),
                    };
                },
            );

        $table = $this->createMock(Table::class);
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
        $matcher = self::exactly(15);
        $table->expects($matcher)
            ->method('addRow')
            ->willReturnCallback(
                static function (TableSeparator | array $row) use ($matcher, $table, $datetime, $level): Table {
                    $invocation = $matcher->numberOfInvocations();

                    if (in_array($invocation, [3, 5, 7, 9], true)) {
                        self::assertInstanceOf(
                            TableSeparator::class,
                            $row,
                            (string) $invocation,
                        );

                        return $table;
                    }

                    self::assertIsArray($row, (string) $invocation);

                    match ($invocation) {
                        4, 8 => self::assertCount(1, $row, (string) $invocation),
                        13 => self::assertCount(3, $row, (string) $invocation),
                        default => self::assertCount(2, $row, (string) $invocation),
                    };

                    if ($invocation === 1) {
                        $tableCell1 = $row[0];
                        assert($tableCell1 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell1, (string) $invocation);
                        self::assertSame('Time', (string) $tableCell1, (string) $invocation);

                        $tableCell2 = $row[1];
                        assert($tableCell2 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell2, (string) $invocation);
                        self::assertSame(
                            $datetime->format(NormalizerFormatter::SIMPLE_DATE),
                            (string) $tableCell2,
                            (string) $invocation,
                        );

                        return $table;
                    }

                    if ($invocation === 2) {
                        $tableCell1 = $row[0];
                        assert($tableCell1 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell1, (string) $invocation);
                        self::assertSame('Level', (string) $tableCell1, (string) $invocation);

                        $tableCell2 = $row[1];
                        assert($tableCell2 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell2, (string) $invocation);
                        self::assertSame($level->getName(), (string) $tableCell2, (string) $invocation);

                        return $table;
                    }

                    if ($invocation === 4) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell, (string) $invocation);
                        self::assertSame('Extra', (string) $tableCell, (string) $invocation);

                        return $table;
                    }

                    if ($invocation === 8) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell, (string) $invocation);
                        self::assertSame('Context', (string) $tableCell, (string) $invocation);
                    }

                    return $table;
                },
            );
        $table->expects(self::once())
            ->method('render');

        $formatter = new StreamFormatter(
            output: $output,
            table: $table,
            format: '%message% %context.five% %extra.app%',
            tableStyle: $tableStyle,
            dateFormat: null,
            allowInlineLineBreaks: false,
        );

        $record = new LogRecord(
            datetime: $datetime,
            channel: $channel,
            level: $level,
            message: $message,
            context: ['one' => null, 'two' => true, 0 => 'numeric-key', 'three' => false, 'four' => ['abc', 'xyz'], 'five' => "test\ntest"],
            extra: ['app' => 'test-app'],
        );

        $formatted = $formatter->format($record);

        self::assertSame($expected, $formatted);
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testFormat6(): void
    {
        $message    = 'test message';
        $channel    = 'test-channel';
        $datetime   = new DateTimeImmutable('now');
        $tableStyle = 'default';
        $level      = Level::Error;

        $expected = 'rendered-content';

        $output  = $this->createMock(BufferedOutput::class);
        $matcher = self::exactly(2);
        $output->expects($matcher)
            ->method('fetch')
            ->willReturnCallback(
                static fn (): string => match ($matcher->numberOfInvocations()) {
                    1 => '',
                    default => $expected,
                },
            );
        $matcher = self::exactly(5);
        $output->expects($matcher)
            ->method('writeln')
            ->willReturnCallback(
                /** @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter */
                static function (string | iterable $messages, int $options = OutputInterface::OUTPUT_NORMAL) use ($matcher): void {
                    $invocation = $matcher->numberOfInvocations();

                    match ($invocation) {
                        1 => self::assertSame(
                            str_repeat('=', StreamFormatter::FULL_WIDTH),
                            $messages,
                            (string) $invocation,
                        ),
                        2, 4, 5 => self::assertSame('', $messages, (string) $invocation),
                        default => self::assertSame(
                            "test message test\ntest test-app",
                            $messages,
                            (string) $invocation,
                        ),
                    };
                },
            );

        $table = $this->createMock(Table::class);
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
        $matcher = self::exactly(15);
        $table->expects($matcher)
            ->method('addRow')
            ->willReturnCallback(
                static function (TableSeparator | array $row) use ($matcher, $table, $datetime, $level): Table {
                    $invocation = $matcher->numberOfInvocations();

                    if (in_array($invocation, [3, 5, 7, 9], true)) {
                        self::assertInstanceOf(
                            TableSeparator::class,
                            $row,
                            (string) $invocation,
                        );

                        return $table;
                    }

                    self::assertIsArray($row, (string) $invocation);

                    match ($invocation) {
                        4, 8 => self::assertCount(1, $row, (string) $invocation),
                        13 => self::assertCount(3, $row, (string) $invocation),
                        default => self::assertCount(2, $row, (string) $invocation),
                    };

                    if ($invocation === 1) {
                        $tableCell1 = $row[0];
                        assert($tableCell1 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell1, (string) $invocation);
                        self::assertSame('Time', (string) $tableCell1, (string) $invocation);

                        $tableCell2 = $row[1];
                        assert($tableCell2 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell2, (string) $invocation);
                        self::assertSame(
                            $datetime->format(NormalizerFormatter::SIMPLE_DATE),
                            (string) $tableCell2,
                            (string) $invocation,
                        );

                        return $table;
                    }

                    if ($invocation === 2) {
                        $tableCell1 = $row[0];
                        assert($tableCell1 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell1, (string) $invocation);
                        self::assertSame('Level', (string) $tableCell1, (string) $invocation);

                        $tableCell2 = $row[1];
                        assert($tableCell2 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell2, (string) $invocation);
                        self::assertSame($level->getName(), (string) $tableCell2, (string) $invocation);

                        return $table;
                    }

                    if ($invocation === 4) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell, (string) $invocation);
                        self::assertSame('Extra', (string) $tableCell, (string) $invocation);

                        return $table;
                    }

                    if ($invocation === 8) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell, (string) $invocation);
                        self::assertSame('Context', (string) $tableCell, (string) $invocation);
                    }

                    return $table;
                },
            );
        $table->expects(self::once())
            ->method('render');

        $formatter = new StreamFormatter(
            output: $output,
            table: $table,
            format: '%message% %context.five% %extra.app%',
            tableStyle: $tableStyle,
            dateFormat: null,
            allowInlineLineBreaks: true,
        );

        $record = new LogRecord(
            datetime: $datetime,
            channel: $channel,
            level: $level,
            message: $message,
            context: ['one' => null, 'two' => true, 'three' => false, 'four' => ['abc', 'xyz'], 'five' => "test\ntest"],
            extra: ['app' => 'test-app', 0 => 'numeric-key'],
        );

        $formatted = $formatter->format($record);

        self::assertSame($expected, $formatted);
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws \PHPUnit\Framework\MockObject\Exception
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

        $output  = $this->createMock(BufferedOutput::class);
        $matcher = self::exactly(2);
        $output->expects($matcher)
            ->method('fetch')
            ->willReturnCallback(
                static fn (): string => match ($matcher->numberOfInvocations()) {
                    1 => '',
                    default => $expected,
                },
            );
        $matcher = self::exactly(5);
        $output->expects($matcher)
            ->method('writeln')
            ->willReturnCallback(
                /** @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter */
                static function (string | iterable $messages, int $options = OutputInterface::OUTPUT_NORMAL) use ($matcher, $exception): void {
                    $invocation = $matcher->numberOfInvocations();

                    match ($invocation) {
                        1 => self::assertSame(
                            str_repeat('=', StreamFormatter::FULL_WIDTH),
                            $messages,
                            (string) $invocation,
                        ),
                        2, 4, 5 => self::assertSame('', $messages, (string) $invocation),
                        default => self::assertSame(
                            "test message test\ntest <[object] (RuntimeException(code: " . $exception->getCode() . '): error at ' . $exception->getFile() . ':' . $exception->getLine() . ')>',
                            $messages,
                            (string) $invocation,
                        ),
                    };
                },
            );

        $table = $this->createMock(Table::class);
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
                    $invocation = $matcher->numberOfInvocations();

                    if (in_array($invocation, [3, 5, 14, 16], true)) {
                        self::assertInstanceOf(
                            TableSeparator::class,
                            $row,
                            (string) $invocation,
                        );

                        return $table;
                    }

                    self::assertIsArray($row, (string) $invocation);

                    match ($invocation) {
                        4, 15 => self::assertCount(
                            1,
                            $row,
                            (string) $invocation,
                        ),
                        7, 20 => self::assertCount(3, $row, (string) $invocation),
                        default => self::assertCount(2, $row, (string) $invocation),
                    };

                    if ($invocation === 1) {
                        $tableCell1 = $row[0];
                        assert($tableCell1 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell1, (string) $invocation);
                        self::assertSame('Time', (string) $tableCell1, (string) $invocation);

                        $tableCell2 = $row[1];
                        assert($tableCell2 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell2, (string) $invocation);
                        self::assertSame(
                            $datetime->format(NormalizerFormatter::SIMPLE_DATE),
                            (string) $tableCell2,
                            (string) $invocation,
                        );

                        return $table;
                    }

                    if ($invocation === 2) {
                        $tableCell1 = $row[0];
                        assert($tableCell1 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell1, (string) $invocation);
                        self::assertSame('Level', (string) $tableCell1, (string) $invocation);

                        $tableCell2 = $row[1];
                        assert($tableCell2 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell2, (string) $invocation);
                        self::assertSame($level->getName(), (string) $tableCell2, (string) $invocation);

                        return $table;
                    }

                    if ($invocation === 4) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell, (string) $invocation);
                        self::assertSame('Extra', (string) $tableCell, (string) $invocation);

                        return $table;
                    }

                    if ($invocation === 7) {
                        $tableCell1 = $row[0];
                        assert($tableCell1 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell1, (string) $invocation);
                        self::assertSame('Throwable', (string) $tableCell1, (string) $invocation);

                        $tableCell2 = $row[1];
                        assert($tableCell2 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell2, (string) $invocation);
                        self::assertSame('Code', (string) $tableCell2, (string) $invocation);

                        return $table;
                    }

                    if ($invocation === 8) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell, (string) $invocation);
                        self::assertSame('File', (string) $tableCell, (string) $invocation);

                        return $table;
                    }

                    if ($invocation === 9) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell, (string) $invocation);
                        self::assertSame('Line', (string) $tableCell, (string) $invocation);

                        return $table;
                    }

                    if ($invocation === 10) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell, (string) $invocation);
                        self::assertSame('Message', (string) $tableCell, (string) $invocation);

                        return $table;
                    }

                    if ($invocation === 11) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell, (string) $invocation);
                        self::assertSame('Trace', (string) $tableCell, (string) $invocation);

                        return $table;
                    }

                    if ($invocation === 12) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell, (string) $invocation);
                        self::assertSame('Type', (string) $tableCell, (string) $invocation);

                        return $table;
                    }

                    if ($invocation === 15) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell, (string) $invocation);
                        self::assertSame('Context', (string) $tableCell, (string) $invocation);
                    }

                    if ($invocation === 20) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell, (string) $invocation);
                        self::assertSame('four', (string) $tableCell, (string) $invocation);

                        return $table;
                    }

                    return $table;
                },
            );
        $table->expects(self::once())
            ->method('render');

        $formatter = new StreamFormatter(
            output: $output,
            table: $table,
            format: '%message% %context.five% <%extra.Exception%>',
            tableStyle: $tableStyle,
            dateFormat: null,
            allowInlineLineBreaks: true,
        );

        $record = new LogRecord(
            datetime: $datetime,
            channel: $channel,
            level: $level,
            message: $message,
            context: ['one' => null, 'two' => true, 'three' => false, 'four' => ['abc', 'xyz'], 'five' => "test\ntest"],
            extra: ['app' => 'test-app', 0 => 'numeric-key', 'Exception' => $exception, 'system' => 'test-system'],
        );

        $formatted = $formatter->format($record);

        self::assertSame($expected, $formatted);
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws \PHPUnit\Framework\MockObject\Exception
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

        $output  = $this->createMock(BufferedOutput::class);
        $matcher = self::exactly(2);
        $output->expects($matcher)
            ->method('fetch')
            ->willReturnCallback(
                static fn (): string => match ($matcher->numberOfInvocations()) {
                    1 => '',
                    default => $expected,
                },
            );
        $matcher = self::exactly(5);
        $output->expects($matcher)
            ->method('writeln')
            ->willReturnCallback(
                /** @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter */
                static function (string | iterable $messages, int $options = OutputInterface::OUTPUT_NORMAL) use ($matcher): void {
                    $invocation = $matcher->numberOfInvocations();

                    match ($invocation) {
                        1 => self::assertSame(
                            str_repeat('=', StreamFormatter::FULL_WIDTH),
                            $messages,
                            (string) $invocation,
                        ),
                        2, 4, 5 => self::assertSame('', $messages, (string) $invocation),
                        default => self::assertSame(
                            "test message test\ntest test-app",
                            $messages,
                            (string) $invocation,
                        ),
                    };
                },
            );

        $table = $this->createMock(Table::class);
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
                    $invocation = $matcher->numberOfInvocations();

                    if (in_array($invocation, [3, 5, 14, 16], true)) {
                        self::assertInstanceOf(
                            TableSeparator::class,
                            $row,
                            (string) $invocation,
                        );

                        return $table;
                    }

                    self::assertIsArray($row, (string) $invocation);

                    match ($invocation) {
                        4, 15 => self::assertCount(
                            1,
                            $row,
                            (string) $invocation,
                        ),
                        7, 20 => self::assertCount(3, $row, (string) $invocation),
                        default => self::assertCount(2, $row, (string) $invocation),
                    };

                    if ($invocation === 1) {
                        $tableCell1 = $row[0];
                        assert($tableCell1 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell1, (string) $invocation);
                        self::assertSame('Time', (string) $tableCell1, (string) $invocation);

                        $tableCell2 = $row[1];
                        assert($tableCell2 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell2, (string) $invocation);
                        self::assertSame(
                            $datetime->format(NormalizerFormatter::SIMPLE_DATE),
                            (string) $tableCell2,
                            (string) $invocation,
                        );

                        return $table;
                    }

                    if ($invocation === 2) {
                        $tableCell1 = $row[0];
                        assert($tableCell1 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell1, (string) $invocation);
                        self::assertSame('Level', (string) $tableCell1, (string) $invocation);

                        $tableCell2 = $row[1];
                        assert($tableCell2 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell2, (string) $invocation);
                        self::assertSame($level->getName(), (string) $tableCell2, (string) $invocation);

                        return $table;
                    }

                    if ($invocation === 4) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell, (string) $invocation);
                        self::assertSame('Extra', (string) $tableCell, (string) $invocation);

                        return $table;
                    }

                    if ($invocation === 7) {
                        $tableCell1 = $row[0];
                        assert($tableCell1 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell1, (string) $invocation);
                        self::assertSame('Throwable', (string) $tableCell1, (string) $invocation);

                        $tableCell2 = $row[1];
                        assert($tableCell2 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell2, (string) $invocation);
                        self::assertSame('Code', (string) $tableCell2, (string) $invocation);

                        return $table;
                    }

                    if ($invocation === 8) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell, (string) $invocation);
                        self::assertSame('File', (string) $tableCell, (string) $invocation);

                        return $table;
                    }

                    if ($invocation === 9) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell, (string) $invocation);
                        self::assertSame('Line', (string) $tableCell, (string) $invocation);

                        return $table;
                    }

                    if ($invocation === 10) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell, (string) $invocation);
                        self::assertSame('Message', (string) $tableCell, (string) $invocation);

                        return $table;
                    }

                    if ($invocation === 11) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell, (string) $invocation);
                        self::assertSame('Trace', (string) $tableCell, (string) $invocation);

                        return $table;
                    }

                    if ($invocation === 12) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell, (string) $invocation);
                        self::assertSame('Type', (string) $tableCell, (string) $invocation);

                        return $table;
                    }

                    if ($invocation === 15) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell, (string) $invocation);
                        self::assertSame('Context', (string) $tableCell, (string) $invocation);
                    }

                    return $table;
                },
            );
        $table->expects(self::once())
            ->method('render');

        $formatter = new StreamFormatter(
            output: $output,
            table: $table,
            format: '%message% %context.five% %extra.app%',
            tableStyle: $tableStyle,
            dateFormat: null,
            allowInlineLineBreaks: true,
        );

        $record = new LogRecord(
            datetime: $datetime,
            channel: $channel,
            level: $level,
            message: $message,
            context: ['one' => null, 'two' => true, 'three' => false, 'four' => ['abc', 'xyz'], 'five' => "test\ntest"],
            extra: ['app' => 'test-app', 0 => 'numeric-key', 'Exception' => $exception, 'system' => 'test-system'],
        );

        $formatted = $formatter->format($record);

        self::assertSame($expected, $formatted);
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws \PHPUnit\Framework\MockObject\Exception
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

        $output  = $this->createMock(BufferedOutput::class);
        $matcher = self::exactly(2);
        $output->expects($matcher)
            ->method('fetch')
            ->willReturnCallback(
                static fn (): string => match ($matcher->numberOfInvocations()) {
                    1 => '',
                    default => $expected,
                },
            );
        $matcher = self::exactly(5);
        $output->expects($matcher)
            ->method('writeln')
            ->willReturnCallback(
                /** @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter */
                static function (string | iterable $messages, int $options = OutputInterface::OUTPUT_NORMAL) use ($matcher): void {
                    $invocation = $matcher->numberOfInvocations();

                    match ($invocation) {
                        1 => self::assertSame(
                            str_repeat('=', StreamFormatter::FULL_WIDTH),
                            $messages,
                            (string) $invocation,
                        ),
                        2, 4, 5 => self::assertSame('', $messages, (string) $invocation),
                        default => self::assertSame(
                            "test message test\ntest test-app",
                            $messages,
                            (string) $invocation,
                        ),
                    };
                },
            );

        $table = $this->createMock(Table::class);
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
                    $invocation = $matcher->numberOfInvocations();

                    if (in_array($invocation, [3, 5, 26, 28], true)) {
                        self::assertInstanceOf(
                            TableSeparator::class,
                            $row,
                            (string) $invocation,
                        );

                        return $table;
                    }

                    self::assertIsArray($row, (string) $invocation);

                    match ($invocation) {
                        4, 27 => self::assertCount(
                            1,
                            $row,
                            (string) $invocation,
                        ),
                        7, 13, 19, 32 => self::assertCount(
                            3,
                            $row,
                            (string) $invocation,
                        ),
                        default => self::assertCount(2, $row, (string) $invocation),
                    };

                    if ($invocation === 1) {
                        $tableCell1 = $row[0];
                        assert($tableCell1 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell1, (string) $invocation);
                        self::assertSame('Time', (string) $tableCell1, (string) $invocation);

                        $tableCell2 = $row[1];
                        assert($tableCell2 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell2, (string) $invocation);
                        self::assertSame(
                            $datetime->format(NormalizerFormatter::SIMPLE_DATE),
                            (string) $tableCell2,
                            (string) $invocation,
                        );

                        return $table;
                    }

                    if ($invocation === 2) {
                        $tableCell1 = $row[0];
                        assert($tableCell1 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell1, (string) $invocation);
                        self::assertSame('Level', (string) $tableCell1, (string) $invocation);

                        $tableCell2 = $row[1];
                        assert($tableCell2 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell2, (string) $invocation);
                        self::assertSame($level->getName(), (string) $tableCell2, (string) $invocation);

                        return $table;
                    }

                    if ($invocation === 4) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell, (string) $invocation);
                        self::assertSame('Extra', (string) $tableCell, (string) $invocation);

                        return $table;
                    }

                    if ($invocation === 7) {
                        $tableCell1 = $row[0];
                        assert($tableCell1 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell1, (string) $invocation);
                        self::assertSame('Throwable', (string) $tableCell1, (string) $invocation);

                        $tableCell2 = $row[1];
                        assert($tableCell2 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell2, (string) $invocation);
                        self::assertSame('Code', (string) $tableCell2, (string) $invocation);

                        return $table;
                    }

                    if ($invocation === 8) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell, (string) $invocation);
                        self::assertSame('File', (string) $tableCell, (string) $invocation);

                        return $table;
                    }

                    if ($invocation === 9) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell, (string) $invocation);
                        self::assertSame('Line', (string) $tableCell, (string) $invocation);

                        return $table;
                    }

                    if ($invocation === 10) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell, (string) $invocation);
                        self::assertSame('Message', (string) $tableCell, (string) $invocation);

                        return $table;
                    }

                    if ($invocation === 11) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell, (string) $invocation);
                        self::assertSame('Trace', (string) $tableCell, (string) $invocation);

                        return $table;
                    }

                    if ($invocation === 12) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell, (string) $invocation);
                        self::assertSame('Type', (string) $tableCell, (string) $invocation);

                        return $table;
                    }

                    if ($invocation === 13) {
                        $tableCell1 = $row[0];
                        assert($tableCell1 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell1, (string) $invocation);
                        self::assertSame(
                            'previous Throwable',
                            (string) $tableCell1,
                            (string) $invocation,
                        );

                        $tableCell2 = $row[1];
                        assert($tableCell2 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell2, (string) $invocation);
                        self::assertSame('Code', (string) $tableCell2, (string) $invocation);

                        return $table;
                    }

                    if ($invocation === 14) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell, (string) $invocation);
                        self::assertSame('File', (string) $tableCell, (string) $invocation);

                        return $table;
                    }

                    if ($invocation === 15) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell, (string) $invocation);
                        self::assertSame('Line', (string) $tableCell, (string) $invocation);

                        return $table;
                    }

                    if ($invocation === 16) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell, (string) $invocation);
                        self::assertSame('Message', (string) $tableCell, (string) $invocation);

                        return $table;
                    }

                    if ($invocation === 17) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell, (string) $invocation);
                        self::assertSame('Trace', (string) $tableCell, (string) $invocation);

                        return $table;
                    }

                    if ($invocation === 18) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell, (string) $invocation);
                        self::assertSame('Type', (string) $tableCell, (string) $invocation);

                        return $table;
                    }

                    if ($invocation === 19) {
                        $tableCell1 = $row[0];
                        assert($tableCell1 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell1, (string) $invocation);
                        self::assertSame(
                            'previous Throwable',
                            (string) $tableCell1,
                            (string) $invocation,
                        );

                        $tableCell2 = $row[1];
                        assert($tableCell2 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell2, (string) $invocation);
                        self::assertSame('Code', (string) $tableCell2, (string) $invocation);

                        return $table;
                    }

                    if ($invocation === 20) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell, (string) $invocation);
                        self::assertSame('File', (string) $tableCell, (string) $invocation);

                        return $table;
                    }

                    if ($invocation === 21) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell, (string) $invocation);
                        self::assertSame('Line', (string) $tableCell, (string) $invocation);

                        return $table;
                    }

                    if ($invocation === 22) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell, (string) $invocation);
                        self::assertSame('Message', (string) $tableCell, (string) $invocation);

                        return $table;
                    }

                    if ($invocation === 23) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell, (string) $invocation);
                        self::assertSame('Trace', (string) $tableCell, (string) $invocation);

                        return $table;
                    }

                    if ($invocation === 24) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell, (string) $invocation);
                        self::assertSame('Type', (string) $tableCell, (string) $invocation);

                        return $table;
                    }

                    if ($invocation === 27) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell, (string) $invocation);
                        self::assertSame('Context', (string) $tableCell, (string) $invocation);
                    }

                    return $table;
                },
            );
        $table->expects(self::once())
            ->method('render');

        $formatter = new StreamFormatter(
            output: $output,
            table: $table,
            format: '%message% %context.five% %extra.app%',
            tableStyle: $tableStyle,
            dateFormat: null,
            allowInlineLineBreaks: true,
        );

        $record = new LogRecord(
            datetime: $datetime,
            channel: $channel,
            level: $level,
            message: $message,
            context: ['one' => null, 'two' => true, 'three' => false, 'four' => ['abc', 'xyz'], 'five' => "test\ntest"],
            extra: ['app' => 'test-app', 0 => 'numeric-key', 'Exception' => $exception3, 'system' => 'test-system'],
        );

        $formatted = $formatter->format($record);

        self::assertSame($expected, $formatted);
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws \PHPUnit\Framework\MockObject\Exception
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

        $output  = $this->createMock(BufferedOutput::class);
        $matcher = self::exactly(2);
        $output->expects($matcher)
            ->method('fetch')
            ->willReturnCallback(
                static fn (): string => match ($matcher->numberOfInvocations()) {
                    1 => '',
                    default => $expected,
                },
            );
        $matcher = self::exactly(5);
        $output->expects($matcher)
            ->method('writeln')
            ->willReturnCallback(
                /** @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter */
                static function (string | iterable $messages, int $options = OutputInterface::OUTPUT_NORMAL) use ($matcher): void {
                    $invocation = $matcher->numberOfInvocations();

                    match ($invocation) {
                        1 => self::assertSame(
                            str_repeat('=', StreamFormatter::FULL_WIDTH),
                            $messages,
                            (string) $invocation,
                        ),
                        2, 4, 5 => self::assertSame('', $messages, (string) $invocation),
                        default => self::assertSame(
                            "test message context.one test\ntest test-app extra.Exception",
                            $messages,
                            (string) $invocation,
                        ),
                    };
                },
            );

        $table = $this->createMock(Table::class);
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
                    $invocation = $matcher->numberOfInvocations();

                    if (in_array($invocation, [3, 5, 26, 28], true)) {
                        self::assertInstanceOf(
                            TableSeparator::class,
                            $row,
                            (string) $invocation,
                        );

                        return $table;
                    }

                    self::assertIsArray($row, (string) $invocation);

                    match ($invocation) {
                        4, 27 => self::assertCount(
                            1,
                            $row,
                            (string) $invocation,
                        ),
                        7, 13, 19, 32 => self::assertCount(
                            3,
                            $row,
                            (string) $invocation,
                        ),
                        default => self::assertCount(2, $row, (string) $invocation),
                    };

                    if ($invocation === 1) {
                        $tableCell1 = $row[0];
                        assert($tableCell1 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell1, (string) $invocation);
                        self::assertSame('Time', (string) $tableCell1, (string) $invocation);

                        $tableCell2 = $row[1];
                        assert($tableCell2 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell2, (string) $invocation);
                        self::assertSame(
                            $datetime->format(NormalizerFormatter::SIMPLE_DATE),
                            (string) $tableCell2,
                            (string) $invocation,
                        );

                        return $table;
                    }

                    if ($invocation === 2) {
                        $tableCell1 = $row[0];
                        assert($tableCell1 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell1, (string) $invocation);
                        self::assertSame('Level', (string) $tableCell1, (string) $invocation);

                        $tableCell2 = $row[1];
                        assert($tableCell2 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell2, (string) $invocation);
                        self::assertSame($level->getName(), (string) $tableCell2, (string) $invocation);

                        return $table;
                    }

                    if ($invocation === 4) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell, (string) $invocation);
                        self::assertSame('Extra', (string) $tableCell, (string) $invocation);

                        return $table;
                    }

                    if ($invocation === 7) {
                        $tableCell1 = $row[0];
                        assert($tableCell1 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell1, (string) $invocation);
                        self::assertSame('Throwable', (string) $tableCell1, (string) $invocation);

                        $tableCell2 = $row[1];
                        assert($tableCell2 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell2, (string) $invocation);
                        self::assertSame('Code', (string) $tableCell2, (string) $invocation);

                        return $table;
                    }

                    if ($invocation === 8) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell, (string) $invocation);
                        self::assertSame('File', (string) $tableCell, (string) $invocation);

                        return $table;
                    }

                    if ($invocation === 9) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell, (string) $invocation);
                        self::assertSame('Line', (string) $tableCell, (string) $invocation);

                        return $table;
                    }

                    if ($invocation === 10) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell, (string) $invocation);
                        self::assertSame('Message', (string) $tableCell, (string) $invocation);

                        return $table;
                    }

                    if ($invocation === 11) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell, (string) $invocation);
                        self::assertSame('Trace', (string) $tableCell, (string) $invocation);

                        return $table;
                    }

                    if ($invocation === 12) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell, (string) $invocation);
                        self::assertSame('Type', (string) $tableCell, (string) $invocation);

                        return $table;
                    }

                    if ($invocation === 13) {
                        $tableCell1 = $row[0];
                        assert($tableCell1 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell1, (string) $invocation);
                        self::assertSame(
                            'previous Throwable',
                            (string) $tableCell1,
                            (string) $invocation,
                        );

                        $tableCell2 = $row[1];
                        assert($tableCell2 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell2, (string) $invocation);
                        self::assertSame('Code', (string) $tableCell2, (string) $invocation);

                        return $table;
                    }

                    if ($invocation === 14) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell, (string) $invocation);
                        self::assertSame('File', (string) $tableCell, (string) $invocation);

                        return $table;
                    }

                    if ($invocation === 15) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell, (string) $invocation);
                        self::assertSame('Line', (string) $tableCell, (string) $invocation);

                        return $table;
                    }

                    if ($invocation === 16) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell, (string) $invocation);
                        self::assertSame('Message', (string) $tableCell, (string) $invocation);

                        return $table;
                    }

                    if ($invocation === 17) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell, (string) $invocation);
                        self::assertSame('Trace', (string) $tableCell, (string) $invocation);

                        return $table;
                    }

                    if ($invocation === 18) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell, (string) $invocation);
                        self::assertSame('Type', (string) $tableCell, (string) $invocation);

                        return $table;
                    }

                    if ($invocation === 19) {
                        $tableCell1 = $row[0];
                        assert($tableCell1 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell1, (string) $invocation);
                        self::assertSame(
                            'previous Throwable',
                            (string) $tableCell1,
                            (string) $invocation,
                        );

                        $tableCell2 = $row[1];
                        assert($tableCell2 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell2, (string) $invocation);
                        self::assertSame('Code', (string) $tableCell2, (string) $invocation);

                        return $table;
                    }

                    if ($invocation === 20) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell, (string) $invocation);
                        self::assertSame('File', (string) $tableCell, (string) $invocation);

                        return $table;
                    }

                    if ($invocation === 21) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell, (string) $invocation);
                        self::assertSame('Line', (string) $tableCell, (string) $invocation);

                        return $table;
                    }

                    if ($invocation === 22) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell, (string) $invocation);
                        self::assertSame('Message', (string) $tableCell, (string) $invocation);

                        return $table;
                    }

                    if ($invocation === 23) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell, (string) $invocation);
                        self::assertSame('Trace', (string) $tableCell, (string) $invocation);

                        return $table;
                    }

                    if ($invocation === 24) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell, (string) $invocation);
                        self::assertSame('Type', (string) $tableCell, (string) $invocation);

                        return $table;
                    }

                    if ($invocation === 27) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell, (string) $invocation);
                        self::assertSame('Context', (string) $tableCell, (string) $invocation);
                    }

                    return $table;
                },
            );
        $table->expects(self::once())
            ->method('render');

        $formatter = new StreamFormatter(
            output: $output,
            table: $table,
            format: '%message% context.one %context.five% %extra.app% extra.Exception',
            tableStyle: $tableStyle,
            dateFormat: null,
            allowInlineLineBreaks: true,
        );

        $record = new LogRecord(
            datetime: $datetime,
            channel: $channel,
            level: $level,
            message: $message,
            context: ['one' => null, 'two' => true, 'three' => false, 'four' => ['abc', 'xyz'], 'five' => "test\ntest"],
            extra: ['app' => 'test-app', 0 => 'numeric-key', 'Exception' => $exception3, 'system' => 'test-system'],
        );

        $formatted = $formatter->format($record);

        self::assertSame($expected, $formatted);
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testFormat11(): void
    {
        $message    = 'test message';
        $channel    = 'test-channel';
        $tableStyle = 'default';
        $datetime   = new DateTimeImmutable('now');
        $level      = Level::Error;

        $expected = 'rendered-content';

        $output  = $this->createMock(BufferedOutput::class);
        $matcher = self::exactly(2);
        $output->expects($matcher)
            ->method('fetch')
            ->willReturnCallback(
                static fn (): string => match ($matcher->numberOfInvocations()) {
                    1 => '',
                    default => $expected,
                },
            );
        $matcher = self::exactly(5);
        $output->expects($matcher)
            ->method('writeln')
            ->willReturnCallback(
                /** @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter */
                static function (string | iterable $messages, int $options = OutputInterface::OUTPUT_NORMAL) use ($matcher): void {
                    $invocation = $matcher->numberOfInvocations();

                    match ($invocation) {
                        1 => self::assertSame(
                            str_repeat('=', StreamFormatter::FULL_WIDTH),
                            $messages,
                            (string) $invocation,
                        ),
                        2, 4, 5 => self::assertSame('', $messages, (string) $invocation),
                        default => self::assertSame(
                            "test message NULL test\ntest  test-app test-app",
                            $messages,
                            (string) $invocation,
                        ),
                    };
                },
            );

        $table = $this->createMock(Table::class);
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
        $matcher = self::exactly(11);
        $table->expects($matcher)
            ->method('addRow')
            ->willReturnCallback(
                static function (TableSeparator | array $row) use ($matcher, $table, $datetime, $level): Table {
                    $invocation = $matcher->numberOfInvocations();

                    if (in_array($invocation, [3, 5, 7, 9], true)) {
                        self::assertInstanceOf(
                            TableSeparator::class,
                            $row,
                            (string) $invocation,
                        );

                        return $table;
                    }

                    self::assertIsArray($row, (string) $invocation);

                    match ($invocation) {
                        4, 8 => self::assertCount(1, $row, (string) $invocation),
                        default => self::assertCount(2, $row, (string) $invocation),
                    };

                    if ($invocation === 1) {
                        $tableCell1 = $row[0];
                        assert($tableCell1 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell1, (string) $invocation);
                        self::assertSame('Time', (string) $tableCell1, (string) $invocation);

                        $tableCell2 = $row[1];
                        assert($tableCell2 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell2, (string) $invocation);
                        self::assertSame(
                            $datetime->format(NormalizerFormatter::SIMPLE_DATE),
                            (string) $tableCell2,
                            (string) $invocation,
                        );

                        return $table;
                    }

                    if ($invocation === 2) {
                        $tableCell1 = $row[0];
                        assert($tableCell1 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell1, (string) $invocation);
                        self::assertSame('Level', (string) $tableCell1, (string) $invocation);

                        $tableCell2 = $row[1];
                        assert($tableCell2 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell2, (string) $invocation);
                        self::assertSame($level->getName(), (string) $tableCell2, (string) $invocation);

                        return $table;
                    }

                    if ($invocation === 4) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell, (string) $invocation);
                        self::assertSame('Extra', (string) $tableCell, (string) $invocation);

                        return $table;
                    }

                    if ($invocation === 8) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell, (string) $invocation);
                        self::assertSame('Context', (string) $tableCell, (string) $invocation);
                    }

                    return $table;
                },
            );
        $table->expects(self::once())
            ->method('render');

        $formatter = new StreamFormatter(
            output: $output,
            table: $table,
            format: '%message% %context.one% %context.five% %context% %extra.app% %extra.app% %extra%',
            tableStyle: $tableStyle,
            dateFormat: null,
            allowInlineLineBreaks: true,
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
     * @throws \PHPUnit\Framework\MockObject\Exception
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

        $output  = $this->createMock(BufferedOutput::class);
        $matcher = self::exactly(2);
        $output->expects($matcher)
            ->method('fetch')
            ->willReturnCallback(
                static fn (): string => match ($matcher->numberOfInvocations()) {
                    1 => '',
                    default => $expected,
                },
            );
        $matcher = self::exactly(5);
        $output->expects($matcher)
            ->method('writeln')
            ->willReturnCallback(
                /** @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter */
                static function (string | iterable $messages, int $options = OutputInterface::OUTPUT_NORMAL) use ($matcher, $formattedMessage): void {
                    $invocation = $matcher->numberOfInvocations();

                    match ($invocation) {
                        1 => self::assertSame(
                            str_repeat('=', StreamFormatter::FULL_WIDTH),
                            $messages,
                            (string) $invocation,
                        ),
                        2, 4, 5 => self::assertSame('', $messages, (string) $invocation),
                        default => self::assertSame($formattedMessage, $messages, (string) $invocation),
                    };
                },
            );

        $table = $this->createMock(Table::class);
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
                    $invocation = $matcher->numberOfInvocations();

                    if (in_array($invocation, [3, 5, 7, 9], true)) {
                        self::assertInstanceOf(
                            TableSeparator::class,
                            $row,
                            (string) $invocation,
                        );

                        return $table;
                    }

                    self::assertIsArray($row, (string) $invocation);

                    match ($invocation) {
                        4, 8 => self::assertCount(1, $row, (string) $invocation),
                        12 => self::assertCount(3, $row, (string) $invocation),
                        default => self::assertCount(2, $row, (string) $invocation),
                    };

                    if ($invocation === 1) {
                        $tableCell1 = $row[0];
                        assert($tableCell1 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell1, (string) $invocation);
                        self::assertSame('Time', (string) $tableCell1, (string) $invocation);

                        $tableCell2 = $row[1];
                        assert($tableCell2 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell2, (string) $invocation);
                        self::assertSame(
                            $datetime->format(NormalizerFormatter::SIMPLE_DATE),
                            (string) $tableCell2,
                            (string) $invocation,
                        );

                        return $table;
                    }

                    if ($invocation === 2) {
                        $tableCell1 = $row[0];
                        assert($tableCell1 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell1, (string) $invocation);
                        self::assertSame('Level', (string) $tableCell1, (string) $invocation);

                        $tableCell2 = $row[1];
                        assert($tableCell2 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell2, (string) $invocation);
                        self::assertSame($level->getName(), (string) $tableCell2, (string) $invocation);

                        return $table;
                    }

                    if ($invocation === 4) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell, (string) $invocation);
                        self::assertSame('Extra', (string) $tableCell, (string) $invocation);

                        return $table;
                    }

                    if ($invocation === 8) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell, (string) $invocation);
                        self::assertSame('Context', (string) $tableCell, (string) $invocation);
                    }

                    return $table;
                },
            );
        $table->expects(self::once())
            ->method('render');

        $formatter = new StreamFormatter(
            output: $output,
            table: $table,
            format: '%message% %context.one% %context.five% %context% %extra.app% %extra.app% %extra%',
            tableStyle: $tableStyle,
            dateFormat: null,
            allowInlineLineBreaks: true,
        );

        $record = new LogRecord(
            datetime: $datetime,
            channel: $channel,
            level: $level,
            message: $message,
            context: ['one' => null, 'five' => "test\ntest", 'six' => $stdClass],
            extra: ['app' => 'test-app'],
        );

        $lineFormatter = $this->createMock(LineFormatter::class);
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
     * @throws \PHPUnit\Framework\MockObject\Exception
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
+----------------------+----------------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
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
|                  one | null                                                                                                                                                                                                                                                |
|                  two | true                                                                                                                                                                                                                                                |
|                three | false                                                                                                                                                                                                                                               |
|                 four | 42                                                                                                                                                                                                                                                  |
|                 five | test                                                                                                                                                                                                                                                |
|                      | test                                                                                                                                                                                                                                                |
|                  six | stdClass             | {"a":"test-channel","b":"test message"}                                                                                                                                                                                      |
|                seven | 47.11                                                                                                                                                                                                                                               |
+----------------------+----------------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+

';

        $output = new BufferedOutput();
        $table  = new Table($output);

        $formatter = new StreamFormatter(
            output: $output,
            table: $table,
            format: '%message% %context.one% %context.five% %context% %extra.app% %extra.app% %extra%',
            tableStyle: $tableStyle,
            dateFormat: null,
            allowInlineLineBreaks: true,
        );

        $record = new LogRecord(
            datetime: $datetime,
            channel: $channel,
            level: $level,
            message: $message,
            context: ['one' => null, 'two' => true, 'three' => false, 'four' => 42, 'five' => "test\ntest", 'six' => $stdClass, 'seven' => 47.11],
            extra: ['app' => 'test-app'],
        );

        $lineFormatter = $this->createMock(LineFormatter::class);
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
     * @throws \PHPUnit\Framework\MockObject\Exception
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
+----------------------+----------------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
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
|                  one | null                                                                                                                                                                                                                                                |
|                 five | test                                                                                                                                                                                                                                                |
|                      | test                                                                                                                                                                                                                                                |
|                  six | stdClass             | {"a":"test-channel","b":" test message "}                                                                                                                                                                                    |
+----------------------+----------------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+

';

        $output = new BufferedOutput();
        $table  = new Table($output);

        $formatter = new StreamFormatter(
            output: $output,
            table: $table,
            format: '%message% %context.one% %context.five% %context% %extra.app% %extra.app% %extra%',
            tableStyle: $tableStyle,
            dateFormat: null,
            allowInlineLineBreaks: true,
        );

        $record = new LogRecord(
            datetime: $datetime,
            channel: $channel,
            level: $level,
            message: $message,
            context: ['one' => null, 'five' => "test\ntest", 'six' => $stdClass],
            extra: ['app' => 'test-app'],
        );

        $lineFormatter = $this->createMock(LineFormatter::class);
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
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testFormat15(): void
    {
        $message1         = 'test message\rtest message 2\ntest message 3\r\ntest message 4';
        $message2         = 'test message 5\rtest message 6\ntest message 7\r\ntest message 8';
        $message3         = "test1\ntest2\rtest3\r\ntest4";
        $channel          = 'test-channel';
        $tableStyle       = 'default';
        $datetime         = new DateTimeImmutable('now');
        $formattedMessage = 'this is a formatted message';
        $stdClass         = new stdClass();
        $stdClass->a      = $channel;
        $stdClass->b      = $message1;
        $level            = Level::Error;
        $appName          = 'test-app';

        $expected = 'rendered-content';

        $output  = $this->createMock(BufferedOutput::class);
        $matcher = self::exactly(2);
        $output->expects($matcher)
            ->method('fetch')
            ->willReturnCallback(
                static fn (): string => match ($matcher->numberOfInvocations()) {
                    1 => '',
                    default => $expected,
                },
            );
        $matcher = self::exactly(5);
        $output->expects($matcher)
            ->method('writeln')
            ->willReturnCallback(
                /** @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter */
                static function (string | iterable $messages, int $options = OutputInterface::OUTPUT_NORMAL) use ($matcher, $formattedMessage): void {
                    $invocation = $matcher->numberOfInvocations();

                    match ($invocation) {
                        1 => self::assertSame(
                            str_repeat('=', StreamFormatter::FULL_WIDTH),
                            $messages,
                            (string) $invocation,
                        ),
                        2, 4, 5 => self::assertSame('', $messages, (string) $invocation),
                        default => self::assertSame($formattedMessage, $messages, (string) $invocation),
                    };
                },
            );

        $table = $this->createMock(Table::class);
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
                static function (TableSeparator | array $row) use ($matcher, $table, $datetime, $level, $message2, $message3, $appName): Table {
                    $invocation = $matcher->numberOfInvocations();

                    if (in_array($invocation, [3, 5, 7, 9], true)) {
                        self::assertInstanceOf(
                            TableSeparator::class,
                            $row,
                            (string) $invocation,
                        );

                        return $table;
                    }

                    self::assertIsArray($row, (string) $invocation);

                    match ($invocation) {
                        4, 8 => self::assertCount(1, $row, (string) $invocation),
                        12 => self::assertCount(3, $row, (string) $invocation),
                        default => self::assertCount(2, $row, (string) $invocation),
                    };

                    if ($invocation === 1) {
                        $tableCell1 = $row[0];
                        assert($tableCell1 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell1, (string) $invocation);
                        self::assertSame('Time', (string) $tableCell1, (string) $invocation);

                        $tableCell2 = $row[1];
                        assert($tableCell2 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell2, (string) $invocation);
                        self::assertSame(
                            $datetime->format(NormalizerFormatter::SIMPLE_DATE),
                            (string) $tableCell2,
                            (string) $invocation,
                        );

                        return $table;
                    }

                    if ($invocation === 2) {
                        $tableCell1 = $row[0];
                        assert($tableCell1 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell1, (string) $invocation);
                        self::assertSame('Level', (string) $tableCell1, (string) $invocation);

                        $tableCell2 = $row[1];
                        assert($tableCell2 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell2, (string) $invocation);
                        self::assertSame($level->getName(), (string) $tableCell2, (string) $invocation);

                        return $table;
                    }

                    if ($invocation === 4) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell, (string) $invocation);
                        self::assertSame('Extra', (string) $tableCell, (string) $invocation);

                        return $table;
                    }

                    if ($invocation === 6) {
                        $tableCell1 = $row[0];
                        assert($tableCell1 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell1, (string) $invocation);
                        self::assertSame('app', (string) $tableCell1, (string) $invocation);

                        $tableCell2 = $row[1];
                        assert($tableCell2 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell2, (string) $invocation);
                        self::assertSame($appName, (string) $tableCell2, (string) $invocation);

                        return $table;
                    }

                    if ($invocation === 8) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell, (string) $invocation);
                        self::assertSame('Context', (string) $tableCell, (string) $invocation);
                    }

                    if ($invocation === 10) {
                        $tableCell1 = $row[0];
                        assert($tableCell1 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell1, (string) $invocation);
                        self::assertSame('one', (string) $tableCell1, (string) $invocation);

                        $tableCell2 = $row[1];
                        assert($tableCell2 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell2, (string) $invocation);
                        self::assertSame('null', (string) $tableCell2, (string) $invocation);

                        return $table;
                    }

                    if ($invocation === 11) {
                        $tableCell1 = $row[0];
                        assert($tableCell1 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell1, (string) $invocation);
                        self::assertSame('five', (string) $tableCell1, (string) $invocation);

                        $tableCell2 = $row[1];
                        assert($tableCell2 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell2, (string) $invocation);
                        self::assertSame(
                            str_replace(
                                ['\\\r\\\n', '\r\n', '\\\r', '\r', '\\\n', '\n', "\r\n", "\r"],
                                "\n",
                                $message3,
                            ),
                            (string) $tableCell2,
                            (string) $invocation,
                        );

                        return $table;
                    }

                    if ($invocation === 12) {
                        $tableCell1 = $row[0];
                        assert($tableCell1 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell1, (string) $invocation);
                        self::assertSame('six', (string) $tableCell1, (string) $invocation);

                        $tableCell2 = $row[1];
                        assert($tableCell2 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell2, (string) $invocation);
                        self::assertSame('stdClass', (string) $tableCell2, (string) $invocation);

                        return $table;
                    }

                    if ($invocation === 13) {
                        $tableCell1 = $row[0];
                        assert($tableCell1 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell1, (string) $invocation);
                        self::assertSame('seven', (string) $tableCell1, (string) $invocation);

                        $tableCell2 = $row[1];
                        assert($tableCell2 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell2, (string) $invocation);
                        self::assertSame(
                            str_replace(
                                ['\\\r\\\n', '\r\n', '\\\r', '\r', '\\\n', '\n', "\r\n", "\r"],
                                "\n",
                                $message2,
                            ),
                            (string) $tableCell2,
                            (string) $invocation,
                        );

                        return $table;
                    }

                    return $table;
                },
            );
        $table->expects(self::once())
            ->method('render');

        $formatter = new StreamFormatter(
            output: $output,
            table: $table,
            format: '%message% %context.one% %context.five% %context% %extra.app% %extra.app% %extra%',
            tableStyle: $tableStyle,
            dateFormat: null,
            allowInlineLineBreaks: true,
        );

        $record = new LogRecord(
            datetime: $datetime,
            channel: $channel,
            level: $level,
            message: $message1,
            context: ['one' => null, 'five' => $message3, 'six' => $stdClass, 'seven' => $message2],
            extra: ['app' => $appName],
        );

        $lineFormatter = $this->createMock(LineFormatter::class);
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
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testFormat16(): void
    {
        $message1         = 'test message\rtest message 2\ntest message 3\r\ntest message 4';
        $message2         = 'test message 5\rtest message 6\ntest message 7\r\ntest message 8';
        $message3         = "test1\ntest2\rtest3\r\ntest4";
        $channel          = 'test-channel';
        $tableStyle       = 'default';
        $datetime         = new DateTimeImmutable('now');
        $formattedMessage = 'this is a formatted message';
        $stdClass         = new stdClass();
        $stdClass->a      = $channel;
        $stdClass->b      = $message1;
        $level            = Level::Error;
        $appName          = 'test-app';

        $expected = <<<TXT
            ==============================================================================================================================================================================================================================================================================

            this is a formatted message

            +----------------------+----------------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
            | General Info                                                                                                                                                                                                                                                               |
            +----------------------+----------------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
            |                 Time | {$datetime->format(NormalizerFormatter::SIMPLE_DATE)}                                                                                                                                                                                                                           |
            |                Level | ERROR                                                                                                                                                                                                                                               |
            +----------------------+----------------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
            | Extra                                                                                                                                                                                                                                                                      |
            +----------------------+----------------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
            |                  app | test-app                                                                                                                                                                                                                                            |
            +----------------------+----------------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
            | Context                                                                                                                                                                                                                                                                    |
            +----------------------+----------------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
            |                  one | null                                                                                                                                                                                                                                                |
            |                 five | test1 test2 test3 test4                                                                                                                                                                                                                             |
            |                  six | stdClass             | {"a":"test-channel","b":"test message\\\\rtest message 2\\\\ntest message 3\\\\r\\\\ntest message 4"}                                                                                                                                |
            |                seven | test message 5\\rtest message 6\\ntest                                                                                                                                                                                                                |
            |                      | message 7\\r\\ntest message 8                                                                                                                                                                                                                         |
            +----------------------+----------------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+


            TXT;

        $output = new BufferedOutput();
        $table  = new Table($output);

        $formatter = new StreamFormatter(
            output: $output,
            table: $table,
            format: '%message% %context.one% %context.five% %context% %extra.app% %extra.app% %extra%',
            tableStyle: $tableStyle,
            dateFormat: null,
            allowInlineLineBreaks: false,
        );

        $record = new LogRecord(
            datetime: $datetime,
            channel: $channel,
            level: $level,
            message: $message1,
            context: ['one' => null, 'five' => $message3, 'six' => $stdClass, 'seven' => $message2],
            extra: ['app' => $appName],
        );

        $lineFormatter = $this->createMock(LineFormatter::class);
        $lineFormatter->expects(self::once())
            ->method('format')
            ->with($record)
            ->willReturn($formattedMessage);

        $formatter->setFormatter($lineFormatter);

        $formatted = $formatter->format($record);

        self::assertSame(
            str_replace(["\r\n", "\r"], "\n", $expected),
            str_replace(["\r\n", "\r"], "\n", $formatted),
        );
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testFormat17(): void
    {
        $message1         = 'test message\rtest message 2\ntest message 3\r\ntest message 4';
        $message2         = 'test message 5\rtest message 6\ntest message 7\r\ntest message 8';
        $message3         = "test1\ntest2\rtest3\r\ntest4";
        $channel          = 'test-channel';
        $tableStyle       = 'default';
        $datetime         = new DateTimeImmutable('now');
        $formattedMessage = 'this is a formatted message';
        $stdClass         = new stdClass();
        $stdClass->a      = $channel;
        $stdClass->b      = $message1;
        $level            = Level::Error;
        $appName          = 'test-app';

        $expected = <<<TXT
            ==============================================================================================================================================================================================================================================================================

            this is a formatted message

            +----------------------+----------------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
            | General Info                                                                                                                                                                                                                                                               |
            +----------------------+----------------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
            |                 Time | {$datetime->format(NormalizerFormatter::SIMPLE_DATE)}                                                                                                                                                                                                                           |
            |                Level | ERROR                                                                                                                                                                                                                                               |
            +----------------------+----------------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
            | Extra                                                                                                                                                                                                                                                                      |
            +----------------------+----------------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
            |                  app | test-app                                                                                                                                                                                                                                            |
            +----------------------+----------------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
            | Context                                                                                                                                                                                                                                                                    |
            +----------------------+----------------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
            |                  one | null                                                                                                                                                                                                                                                |
            |                 five | test1                                                                                                                                                                                                                                               |
            |                      | test2                                                                                                                                                                                                                                               |
            |                      | test3                                                                                                                                                                                                                                               |
            |                      | test4                                                                                                                                                                                                                                               |
            |                  six | stdClass             | {"a":"test-channel","b":"test message                                                                                                                                                                                        |
            |                      |                      | test message 2                                                                                                                                                                                                               |
            |                      |                      | test message 3                                                                                                                                                                                                               |
            |                      |                      | test message 4"}                                                                                                                                                                                                             |
            |                seven | test message 5                                                                                                                                                                                                                                      |
            |                      | test message 6                                                                                                                                                                                                                                      |
            |                      | test message 7                                                                                                                                                                                                                                      |
            |                      | test message 8                                                                                                                                                                                                                                      |
            +----------------------+----------------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+


            TXT;

        $output = new BufferedOutput();
        $table  = new Table($output);

        $formatter = new StreamFormatter(
            output: $output,
            table: $table,
            format: '%message% %context.one% %context.five% %context% %extra.app% %extra.app% %extra%',
            tableStyle: $tableStyle,
            dateFormat: null,
            allowInlineLineBreaks: true,
        );

        $record = new LogRecord(
            datetime: $datetime,
            channel: $channel,
            level: $level,
            message: $message1,
            context: ['one' => null, 'five' => $message3, 'six' => $stdClass, 'seven' => $message2],
            extra: ['app' => $appName],
        );

        $lineFormatter = $this->createMock(LineFormatter::class);
        $lineFormatter->expects(self::once())
            ->method('format')
            ->with($record)
            ->willReturn($formattedMessage);

        $formatter->setFormatter($lineFormatter);

        $formatted = $formatter->format($record);

        self::assertSame(
            str_replace(["\r\n", "\r"], "\n", $expected),
            str_replace(["\r\n", "\r"], "\n", $formatted),
        );
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws \PHPUnit\Framework\MockObject\Exception
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

        $output  = $this->createMock(BufferedOutput::class);
        $matcher = self::exactly(6);
        $output->expects($matcher)
            ->method('fetch')
            ->willReturnCallback(
                static fn (): string => match ($matcher->numberOfInvocations()) {
                    2 => $expected1,
                    4 => $expected2,
                    6 => $expected3,
                    default => '',
                },
            );
        $matcher = self::exactly(15);
        $output->expects($matcher)
            ->method('writeln')
            ->willReturnCallback(
                /** @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter */
                static function (string | iterable $messages, int $options = OutputInterface::OUTPUT_NORMAL) use ($matcher, $message): void {
                    $invocation = $matcher->numberOfInvocations();

                    match ($invocation) {
                        1, 6, 11 => self::assertSame(
                            str_repeat('=', StreamFormatter::FULL_WIDTH),
                            $messages,
                            (string) $invocation,
                        ),
                        2, 4, 5, 7, 9, 10, 12, 14, 15 => self::assertSame(
                            '',
                            $messages,
                            (string) $invocation,
                        ),
                        default => self::assertSame(
                            $message,
                            $messages,
                            (string) $invocation,
                        ),
                    };
                },
            );

        $table = $this->createMock(Table::class);
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
        $matcher = self::exactly(31);
        $table->expects($matcher)
            ->method('addRow')
            ->willReturnCallback(
                static function (TableSeparator | array $row) use ($matcher, $table, $datetime, $level1, $level2): Table {
                    $invocation = $matcher->numberOfInvocations();

                    if (in_array($invocation, [5, 7, 9, 11, 19, 21, 23, 25], true)) {
                        self::assertInstanceOf(
                            TableSeparator::class,
                            $row,
                            (string) $invocation,
                        );

                        return $table;
                    }

                    self::assertIsArray($row, (string) $invocation);

                    match ($invocation) {
                        6, 10, 20, 24 => self::assertCount(
                            1,
                            $row,
                            (string) $invocation,
                        ),
                        15, 29 => self::assertCount(3, $row, (string) $invocation),
                        default => self::assertCount(2, $row, (string) $invocation),
                    };

                    if ($invocation === 1 || $invocation === 3 || $invocation === 17) {
                        $tableCell1 = $row[0];
                        assert($tableCell1 instanceof TableCell);

                        self::assertInstanceOf(
                            TableCell::class,
                            $tableCell1,
                            (string) $invocation,
                        );
                        self::assertSame(
                            'Time',
                            (string) $tableCell1,
                            (string) $invocation,
                        );

                        $tableCell2 = $row[1];
                        assert($tableCell2 instanceof TableCell);

                        self::assertInstanceOf(
                            TableCell::class,
                            $tableCell2,
                            (string) $invocation,
                        );
                        self::assertSame(
                            $datetime->format(NormalizerFormatter::SIMPLE_DATE),
                            (string) $tableCell2,
                            (string) $invocation,
                        );

                        return $table;
                    }

                    if ($invocation === 2 || $invocation === 18) {
                        $tableCell1 = $row[0];
                        assert($tableCell1 instanceof TableCell);

                        self::assertInstanceOf(
                            TableCell::class,
                            $tableCell1,
                            (string) $invocation,
                        );
                        self::assertSame(
                            'Level',
                            (string) $tableCell1,
                            (string) $invocation,
                        );

                        $tableCell2 = $row[1];
                        assert($tableCell2 instanceof TableCell);

                        self::assertInstanceOf(
                            TableCell::class,
                            $tableCell2,
                            (string) $invocation,
                        );
                        self::assertSame(
                            $level1->getName(),
                            (string) $tableCell2,
                            (string) $invocation,
                        );

                        return $table;
                    }

                    if ($invocation === 4) {
                        $tableCell1 = $row[0];
                        assert($tableCell1 instanceof TableCell);

                        self::assertInstanceOf(
                            TableCell::class,
                            $tableCell1,
                            (string) $invocation,
                        );
                        self::assertSame(
                            'Level',
                            (string) $tableCell1,
                            (string) $invocation,
                        );

                        $tableCell2 = $row[1];
                        assert($tableCell2 instanceof TableCell);

                        self::assertInstanceOf(
                            TableCell::class,
                            $tableCell2,
                            (string) $invocation,
                        );
                        self::assertSame(
                            $level2->getName(),
                            (string) $tableCell2,
                            (string) $invocation,
                        );

                        return $table;
                    }

                    if ($invocation === 6) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(
                            TableCell::class,
                            $tableCell,
                            (string) $invocation,
                        );
                        self::assertSame(
                            'Extra',
                            (string) $tableCell,
                            (string) $invocation,
                        );

                        return $table;
                    }

                    if ($invocation === 10) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(
                            TableCell::class,
                            $tableCell,
                            (string) $invocation,
                        );
                        self::assertSame(
                            'Context',
                            (string) $tableCell,
                            (string) $invocation,
                        );
                    }

                    if ($invocation === 26) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(
                            TableCell::class,
                            $tableCell,
                            (string) $invocation,
                        );
                        self::assertSame(
                            'one',
                            (string) $tableCell,
                            (string) $invocation,
                        );
                    }

                    return $table;
                },
            );
        $table->expects(self::exactly(3))
            ->method('render');

        $formatter = new StreamFormatter(output: $output, table: $table);

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

┌──────────────────────┬──────────────────────┬──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┐
│ General Info                                                                                                                                                                                                                                                               │
├──────────────────────┼──────────────────────┼──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│                 Time │ ' . $datetime->format(
            NormalizerFormatter::SIMPLE_DATE,
        ) . '                                                                                                                                                                                                                           │
│                Level │ ERROR                                                                                                                                                                                                                                               │
└──────────────────────┴──────────────────────┴──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┘

';
        $expected2 = '==============================================================================================================================================================================================================================================================================

test message

┌──────────────────────┬──────────────────────┬──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┐
│ General Info                                                                                                                                                                                                                                                               │
├──────────────────────┼──────────────────────┼──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┤
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
│                  one │ null                                                                                                                                                                                                                                                │
│                  two │ true                                                                                                                                                                                                                                                │
│                three │ false                                                                                                                                                                                                                                               │
│                 four │ 0                    │ abc                                                                                                                                                                                                                          │
│                      │ 1                    │ xyz                                                                                                                                                                                                                          │
└──────────────────────┴──────────────────────┴──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┘

';
        $expected3 = '==============================================================================================================================================================================================================================================================================

test message

┌──────────────────────┬──────────────────────┬──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┐
│ General Info                                                                                                                                                                                                                                                               │
├──────────────────────┼──────────────────────┼──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┤
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
│                  one │ null                                                                                                                                                                                                                                                │
│                  two │ true                                                                                                                                                                                                                                                │
│                three │ false                                                                                                                                                                                                                                               │
│                 four │ 0                    │ abc                                                                                                                                                                                                                          │
│                      │ 1                    │ xyz                                                                                                                                                                                                                          │
│                 five │ test test                                                                                                                                                                                                                                           │
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

        $formatter = new StreamFormatter(
            output: $output,
            table: $table,
            format: null,
            tableStyle: $tableStyle,
        );

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

┌──────────────────────┬──────────────────────┬──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┐
│ General Info                                                                                                                                                                                                                                                               │
├──────────────────────┼──────────────────────┼──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│                 Time │ ' . $datetime->format(
            NormalizerFormatter::SIMPLE_DATE,
        ) . '                                                                                                                                                                                                                           │
│                Level │ ERROR                                                                                                                                                                                                                                               │
└──────────────────────┴──────────────────────┴──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┘

';
        $expected2 = '==============================================================================================================================================================================================================================================================================

test message

┌──────────────────────┬──────────────────────┬──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┐
│ General Info                                                                                                                                                                                                                                                               │
├──────────────────────┼──────────────────────┼──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│                 Time │ ' . $datetime->format(
            NormalizerFormatter::SIMPLE_DATE,
        ) . '                                                                                                                                                                                                                           │
│                Level │ ERROR                                                                                                                                                                                                                                               │
├──────────────────────┼──────────────────────┼──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ Context                                                                                                                                                                                                                                                                    │
├──────────────────────┼──────────────────────┼──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│                  one │ null                                                                                                                                                                                                                                                │
│                  two │ true                                                                                                                                                                                                                                                │
│                three │ false                                                                                                                                                                                                                                               │
│                 four │ 0                    │ abc                                                                                                                                                                                                                          │
│                      │ 1                    │ xyz                                                                                                                                                                                                                          │
└──────────────────────┴──────────────────────┴──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┘

';
        $expected3 = '==============================================================================================================================================================================================================================================================================

test message

┌──────────────────────┬──────────────────────┬──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┐
│ General Info                                                                                                                                                                                                                                                               │
├──────────────────────┼──────────────────────┼──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┤
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

        $formatter = new StreamFormatter(
            output: $output,
            table: $table,
            format: null,
            tableStyle: $tableStyle,
        );

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
    public function testFormatBatch4(): void
    {
        $message    = ' test message ';
        $channel    = 'test-channel';
        $tableStyle = StreamFormatter::BOX_STYLE;
        $datetime   = new DateTimeImmutable('now');

        $expected1 = '==============================================================================================================================================================================================================================================================================

test message

┌──────────────────────┬──────────────────────┬──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┐
│ General Info                                                                                                                                                                                                                                                               │
├──────────────────────┼──────────────────────┼──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│                 Time │ ' . $datetime->format(
            NormalizerFormatter::SIMPLE_DATE,
        ) . '                                                                                                                                                                                                                           │
│                Level │ ERROR                                                                                                                                                                                                                                               │
└──────────────────────┴──────────────────────┴──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┘

';
        $expected2 = '==============================================================================================================================================================================================================================================================================

test message

┌──────────────────────┬──────────────────────┬──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┐
│ General Info                                                                                                                                                                                                                                                               │
├──────────────────────┼──────────────────────┼──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│                 Time │ ' . $datetime->format(
            NormalizerFormatter::SIMPLE_DATE,
        ) . '                                                                                                                                                                                                                           │
│                Level │ ERROR                                                                                                                                                                                                                                               │
├──────────────────────┼──────────────────────┼──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ Context                                                                                                                                                                                                                                                                    │
├──────────────────────┼──────────────────────┼──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│                  one │ null                                                                                                                                                                                                                                                │
│                  two │ true                                                                                                                                                                                                                                                │
│                three │ false                                                                                                                                                                                                                                               │
│            four five │ 0                    │ abc                                                                                                                                                                                                                          │
│                      │ 1                    │ xyz                                                                                                                                                                                                                          │
└──────────────────────┴──────────────────────┴──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┘

';
        $expected3 = '==============================================================================================================================================================================================================================================================================

test message

┌──────────────────────┬──────────────────────┬──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┐
│ General Info                                                                                                                                                                                                                                                               │
├──────────────────────┼──────────────────────┼──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┤
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
            context: ['one' => null, 'two' => true, 'three' => false, ' four_five ' => ['abc', 'xyz']],
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

        $formatter = new StreamFormatter(
            output: $output,
            table: $table,
            format: null,
            tableStyle: $tableStyle,
        );

        $formatted = $formatter->formatBatch([$record1, $record2, $record3]);

        file_put_contents('output.txt', $formatted);

        self::assertSame(
            str_replace("\r\n", "\n", $expected1 . $expected2 . $expected3),
            str_replace("\r\n", "\n", $formatted),
        );
    }
}
