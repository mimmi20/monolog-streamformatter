<?php
/**
 * This file is part of the mimmi20/monolog-streamformatter package.
 *
 * Copyright (c) 2022-2024, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Mimmi20\Monolog\Formatter;

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
use function is_scalar;
use function is_string;
use function str_repeat;
use function str_replace;
use function trim;

final class StreamFormatter extends NormalizerFormatter
{
    /** @api */
    public const SIMPLE_FORMAT = '%message%';

    /** @api */
    public const BOX_STYLE = 'box';

    /** @api */
    public const WIDTH_FIRST_COLUMN = 20;

    /** @api */
    public const WIDTH_SECOND_COLUMN = 20;

    /** @api */
    public const WIDTH_THIRD_COLUMN = 220;

    /** @api */
    public const FULL_WIDTH = self::WIDTH_FIRST_COLUMN + self::WIDTH_SECOND_COLUMN + self::WIDTH_THIRD_COLUMN + 10;

    /** @api */
    public const SPAN_ALL_COLUMS = 3;

    /** @api */
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
        $this->table->setColumnWidths(
            [self::WIDTH_FIRST_COLUMN, self::WIDTH_SECOND_COLUMN, self::WIDTH_THIRD_COLUMN],
        );
    }

    /**
     * @throws void
     *
     * @api
     */
    public function includeStacktraces(bool $include = true): self
    {
        $this->includeStacktraces = $include;

        if ($this->includeStacktraces) {
            $this->allowInlineLineBreaks();
        }

        return $this;
    }

    /**
     * @throws void
     *
     * @api
     */
    public function allowInlineLineBreaks(bool $allow = true): self
    {
        $this->allowInlineLineBreaks = $allow;

        return $this;
    }

    /**
     * @throws void
     *
     * @api
     */
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

        $this->table->addRow(
            [
                new TableCell(
                    'Time',
                    ['style' => new TableCellStyle(['align' => 'right'])],
                ),
                new TableCell(
                    $record->datetime->format($this->dateFormat),
                    ['colspan' => self::SPAN_LAST_COLUMNS],
                ),
            ],
        );
        $this->table->addRow(
            [
                new TableCell(
                    'Level',
                    ['style' => new TableCellStyle(['align' => 'right'])],
                ),
                new TableCell(
                    $levelName,
                    ['colspan' => self::SPAN_LAST_COLUMNS],
                ),
            ],
        );

        $this->addExtraData($record->extra, 'Extra');
        $this->addExtraData($record->context, 'Context');

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
        if ($this->formatter === null) {
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

    /**
     * @param array<mixed> $extraData
     *
     * @throws RuntimeException
     */
    private function addExtraData(array $extraData, string $title): void
    {
        if ($extraData === []) {
            return;
        }

        $this->table->addRow(new TableSeparator());
        $this->table->addRow(
            [new TableCell($title, ['colspan' => self::SPAN_ALL_COLUMS])],
        );
        $this->table->addRow(new TableSeparator());

        foreach ($extraData as $key => $value) {
            if (!is_string($key)) {
                continue;
            }

            if ($value instanceof Throwable) {
                $this->addThrowable($value);

                continue;
            }

            $this->addFact($key, $this->normalize($value));
        }
    }

    /** @throws RuntimeException */
    private function addThrowable(Throwable $exception): void
    {
        $value = [
            'Code' => $exception->getCode(),
            'File' => $exception->getFile(),
            'Line' => $exception->getLine(),
            'Message' => $exception->getMessage(),
            'Trace' => $exception->getTraceAsString(),
            'Type' => $exception::class,
        ];

        $this->addFact('Throwable', $value);

        $prev = $exception->getPrevious();

        if (!$prev instanceof Throwable) {
            return;
        }

        do {
            $value = [
                'Code' => $prev->getCode(),
                'File' => $prev->getFile(),
                'Line' => $prev->getLine(),
                'Message' => $prev->getMessage(),
                'Trace' => $prev->getTraceAsString(),
                'Type' => $prev::class,
            ];

            $this->addFact('previous Throwable', $value);

            $prev = $prev->getPrevious();
        } while ($prev instanceof Throwable);
    }

    /** @throws RuntimeException if encoding fails and errors are not ignored */
    private function addFact(string $name, mixed $value): void
    {
        $name = trim(str_replace('_', ' ', $name));

        if (is_array($value)) {
            $rowspan = count($value);

            foreach (array_keys($value) as $number => $key) {
                $cellValue = $this->stringify($value[$key]);

                if ($number === 0) {
                    $this->table->addRow(
                        [
                            new TableCell(
                                $name,
                                [
                                    'rowspan' => $rowspan,
                                    'style' => new TableCellStyle(
                                        ['align' => 'right'],
                                    ),
                                ],
                            ),
                            new TableCell(
                                (string) $key,
                            ),
                            new TableCell(
                                $cellValue,
                            ),
                        ],
                    );

                    continue;
                }

                $this->table->addRow([new TableCell((string) $key), new TableCell($cellValue)]);
            }

            return;
        }

        $value = $this->stringify($value);

        $this->table->addRow(
            [
                new TableCell($name, ['style' => new TableCellStyle(['align' => 'right'])]),
                new TableCell(
                    $value,
                    ['colspan' => self::SPAN_LAST_COLUMNS],
                ),
            ],
        );
    }

    /** @throws RuntimeException if encoding fails and errors are not ignored */
    private function stringify(mixed $value): string
    {
        return $this->replaceNewlines($this->convertToString($value));
    }

    /** @throws RuntimeException if encoding fails and errors are not ignored */
    private function convertToString(mixed $data): string
    {
        if (is_string($data)) {
            return $data;
        }

        if ($data === null) {
            return 'null';
        }

        if ($data === true) {
            return 'true';
        }

        if ($data === false) {
            return 'false';
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
            return str_replace(
                ['\\\r\\\n', '\r\n', '\\\r', '\r', '\\\n', '\n', "\r\n", "\r"],
                "\n",
                $str,
            );
        }

        return str_replace(["\r\n", "\r", "\n"], ' ', $str);
    }
}
