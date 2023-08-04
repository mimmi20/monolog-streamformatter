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
use Monolog\Formatter\NormalizerFormatter;
use Monolog\Level;
use Monolog\LogRecord;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

use function assert;
use function in_array;
use function str_repeat;

final class StreamFormatter2Test extends TestCase
{
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
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame(str_repeat('=', StreamFormatter::FULL_WIDTH), $messages),
                        2, 4, 5 => self::assertSame('', $messages),
                        default => self::assertSame('test message ["abc","xyz"] test-app', $messages),
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
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame(str_repeat('=', StreamFormatter::FULL_WIDTH), $messages),
                        2, 4, 5 => self::assertSame('', $messages),
                        default => self::assertSame('test message test test test-app', $messages),
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
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame(str_repeat('=', StreamFormatter::FULL_WIDTH), $messages),
                        2, 4, 5 => self::assertSame('', $messages),
                        default => self::assertSame("test message test\ntest test-app", $messages),
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
        $matcher = self::exactly(23);
        $table->expects($matcher)
            ->method('addRow')
            ->willReturnCallback(
                static function (TableSeparator | array $row) use ($matcher, $table, $datetime, $level): Table {
                    if (in_array($matcher->numberOfInvocations(), [4, 6, 15, 17], true)) {
                        self::assertInstanceOf(
                            TableSeparator::class,
                            $row,
                            (string) $matcher->numberOfInvocations(),
                        );

                        return $table;
                    }

                    self::assertIsArray($row, (string) $matcher->numberOfInvocations());

                    match ($matcher->numberOfInvocations()) {
                        1, 5, 16 => self::assertCount(
                            1,
                            $row,
                            (string) $matcher->numberOfInvocations(),
                        ),
                        8, 21 => self::assertCount(3, $row, (string) $matcher->numberOfInvocations()),
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
                        $tableCell1 = $row[0];
                        assert($tableCell1 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell1);
                        self::assertSame('Throwable', (string) $tableCell1);

                        $tableCell2 = $row[1];
                        assert($tableCell2 instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell2);
                        self::assertSame('Code', (string) $tableCell2);

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

                    if ($matcher->numberOfInvocations() === 16) {
                        $tableCell = $row[0];
                        assert($tableCell instanceof TableCell);

                        self::assertInstanceOf(TableCell::class, $tableCell);
                        self::assertSame('Context', (string) $tableCell);
                    }

                    if ($matcher->numberOfInvocations() === 21) {
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
}
