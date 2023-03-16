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

namespace Mimmi20\Monolog\Formatter;

use DateTimeImmutable;
use Monolog\Formatter\LineFormatter;
use Monolog\Formatter\NormalizerFormatter;
use Monolog\Level;
use Monolog\LogRecord;
use RuntimeException;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableCellStyle;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Output\BufferedOutput;
use Throwable;

use function array_keys;
use function count;
use function is_array;
use function is_bool;
use function is_iterable;
use function is_scalar;
use function is_string;
use function mb_strpos;
use function str_repeat;
use function str_replace;
use function trim;
use function ucfirst;
use function var_export;

final class StreamFormatter extends NormalizerFormatter
{
    public const SIMPLE_FORMAT = '%message%';

    public const BOX_STYLE = 'box';

    public const WIDTH_FIRST_COLUMN = 20;

    public const WIDTH_SECOND_COLUMN = 20;

    public const WIDTH_THIRD_COLUMN = 220;

    public const FULL_WIDTH = self::WIDTH_FIRST_COLUMN + self::WIDTH_SECOND_COLUMN + self::WIDTH_THIRD_COLUMN + 10;

    public const SPAN_ALL_COLUMS = 3;

    public const SPAN_LAST_COLUMNS = 2;

    private readonly string $format;
    private bool $allowInlineLineBreaks;
    private bool $includeStacktraces;
    private LineFormatter | null $formatter = null;

    /**
     * @param string|null $format                The format of the message
     * @param string|null $dateFormat            The format of the timestamp: one supported by DateTime::format
     * @param bool        $allowInlineLineBreaks Whether to allow inline line breaks in log entries
     *
     * @throws RuntimeException
     */
    public function __construct(
        private readonly BufferedOutput $output,
        private readonly Table $table,
        string | null $format = null,
        private readonly string $tableStyle = self::BOX_STYLE,
        string | null $dateFormat = null,
        bool $allowInlineLineBreaks = false,
        bool $includeStacktraces = false,
    ) {
        $this->format = $format ?? self::SIMPLE_FORMAT;
        $this->allowInlineLineBreaks($allowInlineLineBreaks);
        $this->includeStacktraces($includeStacktraces);

        parent::__construct($dateFormat);

        $this->table->setStyle($this->tableStyle);
        $this->table->setColumnMaxWidth(0, self::WIDTH_FIRST_COLUMN);
        $this->table->setColumnMaxWidth(1, self::WIDTH_SECOND_COLUMN);
        $this->table->setColumnMaxWidth(2, self::WIDTH_THIRD_COLUMN);
        $this->table->setColumnWidths([self::WIDTH_FIRST_COLUMN, self::WIDTH_SECOND_COLUMN, self::WIDTH_THIRD_COLUMN]);
    }

    /** @throws void */
    public function includeStacktraces(bool $include = true): self
    {
        $this->includeStacktraces = $include;

        if ($this->includeStacktraces) {
            $this->allowInlineLineBreaks();
        }

        return $this;
    }

    /** @throws void */
    public function allowInlineLineBreaks(bool $allow = true): self
    {
        $this->allowInlineLineBreaks = $allow;

        return $this;
    }

    /** @throws void */
    public function setFormatter(LineFormatter $formatter): void
    {
        $this->formatter = $formatter;
    }

    /**
     * Formats a log record.
     *
     * @return string The formatted record
     *
     * @throws RuntimeException if encoding fails and errors are not ignored
     */
    public function format(LogRecord $record): string
    {
        /** @var scalar|array<(array|scalar|null)>|null $vars */
        /** @phpstan-var array{message: string, context: array<mixed>, level: Level, level_name: string, channel: string, datetime: DateTimeImmutable, extra: array<mixed>} $vars */
        $vars = $this->normalizeRecord($record);

        $message = $this->getFormatter()->format($record);

        $levelName = Level::fromValue($record->level->value)->getName();

        // reset output and table rows
        $this->output->fetch();
        $this->table->setRows([]);

        $this->output->writeln(str_repeat('=', self::FULL_WIDTH));
        $this->output->writeln('');
        $this->output->writeln(trim($message));
        $this->output->writeln('');

        $this->table->addRow([new TableCell('General Info', ['colspan' => self::SPAN_ALL_COLUMS])]);

        $this->table->addRow([new TableCell('Time', ['style' => new TableCellStyle(['align' => 'right'])]), new TableCell($record->datetime->format($this->dateFormat), ['colspan' => self::SPAN_LAST_COLUMNS])]);
        $this->table->addRow([new TableCell('Level', ['style' => new TableCellStyle(['align' => 'right'])]), new TableCell($levelName, ['colspan' => self::SPAN_LAST_COLUMNS])]);

        foreach (['extra', 'context'] as $element) {
            if (empty($vars[$element]) || !is_iterable($vars[$element])) {
                continue;
            }

            $this->table->addRow(new TableSeparator());
            $this->table->addRow([new TableCell(ucfirst($element), ['colspan' => self::SPAN_ALL_COLUMS])]);
            $this->table->addRow(new TableSeparator());

            foreach ($vars[$element] as $key => $value) {
                if (!is_string($key)) {
                    continue;
                }

                if (is_array($record->{$element}) && isset($record->{$element}[$key]) && $record->{$element}[$key] instanceof Throwable) {
                    $exception = $record->{$element}[$key];

                    $value = [
                        'Type' => $exception::class,
                        'Message' => $exception->getMessage(),
                        'Code' => $exception->getCode(),
                        'File' => $exception->getFile(),
                        'Line' => $exception->getLine(),
                        'Trace' => $exception->getTraceAsString(),
                    ];

                    $this->addFact($key, $value);

                    $prev = $exception->getPrevious();

                    if ($prev instanceof Throwable) {
                        do {
                            $value = [
                                'Type' => $prev::class,
                                'Message' => $prev->getMessage(),
                                'Code' => $prev->getCode(),
                                'File' => $prev->getFile(),
                                'Line' => $prev->getLine(),
                                'Trace' => $prev->getTraceAsString(),
                            ];

                            $this->addFact('previous Throwable', $value);

                            $prev = $prev->getPrevious();
                        } while ($prev instanceof Throwable);
                    }

                    continue;
                }

                $this->addFact($key, $value);
            }
        }

        $this->table->render();

        $this->output->writeln('');

        return $this->output->fetch();
    }

    /**
     * @param array<array> $records
     * @phpstan-param array<LogRecord> $records
     *
     * @throws RuntimeException if encoding fails and errors are not ignored
     */
    public function formatBatch(array $records): string
    {
        $message = '';

        foreach ($records as $record) {
            $message .= $this->format($record);
        }

        return $message;
    }

    /** @throws RuntimeException */
    private function getFormatter(): LineFormatter
    {
        if (null === $this->formatter) {
            $this->formatter = new LineFormatter(
                format: $this->format,
                dateFormat: $this->dateFormat,
                allowInlineLineBreaks: $this->allowInlineLineBreaks,
                ignoreEmptyContextAndExtra: true,
                includeStacktraces: $this->includeStacktraces,
            );
        }

        return $this->formatter;
    }

    /** @throws RuntimeException if encoding fails and errors are not ignored */
    private function stringify(mixed $value): string
    {
        return $this->replaceNewlines($this->convertToString($value));
    }

    /** @throws RuntimeException if encoding fails and errors are not ignored */
    private function convertToString(mixed $data): string
    {
        if (null === $data || is_bool($data)) {
            return var_export($data, true);
        }

        if (is_scalar($data)) {
            return (string) $data;
        }

        return $this->toJson($data, true);
    }

    /** @throws void */
    private function replaceNewlines(string $str): string
    {
        if ($this->allowInlineLineBreaks) {
            if (0 === mb_strpos($str, '{')) {
                return str_replace(['\r', '\n'], ["\r", "\n"], $str);
            }

            return $str;
        }

        return str_replace(["\r\n", "\r", "\n"], ' ', $str);
    }

    /** @throws RuntimeException if encoding fails and errors are not ignored */
    private function addFact(string $name, mixed $value): void
    {
        $name = trim(str_replace('_', ' ', $name));

        if (is_array($value)) {
            $rowspan = count($value);

            foreach (array_keys($value) as $number => $key) {
                $cellValue = $value[$key];

                if (!is_string($cellValue)) {
                    $cellValue = $this->stringify($cellValue);
                }

                if (0 === $number) {
                    $this->table->addRow([new TableCell($name, ['rowspan' => $rowspan, 'style' => new TableCellStyle(['align' => 'right'])]), new TableCell((string) $key), new TableCell($cellValue)]);
                } else {
                    $this->table->addRow([new TableCell((string) $key), new TableCell($cellValue)]);
                }
            }

            return;
        }

        if (!is_string($value)) {
            $value = $this->stringify($value);
        }

        $this->table->addRow([new TableCell($name, ['style' => new TableCellStyle(['align' => 'right'])]), new TableCell($value, ['colspan' => self::SPAN_LAST_COLUMNS])]);
    }
}
