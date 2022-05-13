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

namespace Mimmi20\Monolog\Formatter;

use DateTimeImmutable;
use Monolog\Formatter\NormalizerFormatter;
use Monolog\Logger;
use RuntimeException;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableCellStyle;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Output\BufferedOutput;
use Throwable;

use function array_keys;
use function count;
use function get_class;
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

/**
 * @phpstan-import-type Level from Logger
 * @phpstan-import-type LevelName from Logger
 * @phpstan-import-type Record from Logger
 */
final class StreamFormatter extends NormalizerFormatter
{
    public const SIMPLE_FORMAT = '%message%';
    public const BOX_STYLE     = 'box';

    private string $format;
    private string $tableStyle;
    private bool $allowInlineLineBreaks;
    private bool $includeStacktraces;

    /**
     * @param string|null $format                The format of the message
     * @param string|null $dateFormat            The format of the timestamp: one supported by DateTime::format
     * @param bool        $allowInlineLineBreaks Whether to allow inline line breaks in log entries
     *
     * @throws void
     */
    public function __construct(?string $format = null, string $tableStyle = self::BOX_STYLE, ?string $dateFormat = null, bool $allowInlineLineBreaks = false, bool $includeStacktraces = false)
    {
        $this->format     = $format ?? self::SIMPLE_FORMAT;
        $this->tableStyle = $tableStyle;
        $this->allowInlineLineBreaks($allowInlineLineBreaks);
        $this->includeStacktraces($includeStacktraces);

        parent::__construct($dateFormat);
    }

    /**
     * @throws void
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
     */
    public function allowInlineLineBreaks(bool $allow = true): self
    {
        $this->allowInlineLineBreaks = $allow;

        return $this;
    }

    /**
     * Formats a log record.
     *
     * @param  array $record A record to format
     * @phpstan-param Record $record
     *
     * @return string The formatted record
     *
     * @throws RuntimeException if encoding fails and errors are not ignored
     */
    public function format(array $record): string
    {
        /** @var scalar|array<(array|scalar|null)>|null $vars */
        /** @phpstan-var array{message: string, context: mixed[], level: Level, level_name: LevelName, channel: string, datetime: DateTimeImmutable, extra: mixed[]} $vars */
        $vars = parent::format($record);

        $message = $this->format;

        foreach ($vars['extra'] as $var => $val) {
            if (false === mb_strpos($message, '%extra.' . $var . '%')) {
                continue;
            }

            $message = str_replace('%extra.' . $var . '%', $this->stringify($val), $message);
            unset($vars['extra'][$var]);
        }

        foreach ($vars['context'] as $var => $val) {
            if (false === mb_strpos($message, '%context.' . $var . '%')) {
                continue;
            }

            $message = str_replace('%context.' . $var . '%', $this->stringify($val), $message);
            unset($vars['context'][$var]);
        }

        if (empty($vars['context'])) {
            unset($vars['context']);
            $message = str_replace('%context%', '', $message);
        }

        if (empty($vars['extra'])) {
            unset($vars['extra']);
            $message = str_replace('%extra%', '', $message);
        }

        foreach ($vars as $var => $val) {
            if (false === mb_strpos($message, '%' . $var . '%')) {
                continue;
            }

            $message = str_replace('%' . $var . '%', $this->stringify($val), $message);
            unset($vars[$var]);
        }

        $output = new BufferedOutput();
        $output->writeln(str_repeat('=', 220));
        $output->writeln('');
        $output->writeln($message);
        $output->writeln('');

        $table = new Table($output);
        $table->setStyle($this->tableStyle);
        $table->setColumnMaxWidth(0, 20);
        $table->setColumnMaxWidth(1, 20);
        $table->setColumnMaxWidth(2, 220);
        $table->setColumnWidths([20, 20, 220]);
        $table->setHeaderTitle($record['level_name']);
        $table->setHeaders([new TableCell('General Info', ['colspan' => 3])]);

        $table->addRow([new TableCell('Time', ['style' => new TableCellStyle(['align' => 'right'])]), new TableCell($record['datetime']->format($this->dateFormat), ['colspan' => 2])]);
        $table->addRow([new TableCell('Level', ['style' => new TableCellStyle(['align' => 'right'])]), new TableCell($record['level_name'], ['colspan' => 2])]);

        $output->writeln('');

        foreach (['extra', 'context'] as $element) {
            if (empty($vars[$element]) || !is_iterable($vars[$element])) {
                continue;
            }

            $table->addRow(new TableSeparator());
            $table->addRow([new TableCell(ucfirst($element), ['colspan' => 3])]);
            $table->addRow(new TableSeparator());

            foreach ($vars[$element] as $key => $value) {
                if (isset($record[$element][$key]) && $record[$element][$key] instanceof Throwable) {
                    $exception = $record[$element][$key];

                    $value = [
                        'Type' => get_class($exception),
                        'Message' => $exception->getMessage(),
                        'Code' => $exception->getCode(),
                        'File' => $exception->getFile(),
                        'Line' => $exception->getLine(),
                        'Trace' => $exception->getTraceAsString(),
                    ];

                    $this->addFact($table, $key, $value);

                    $prev = $exception->getPrevious();

                    if ($prev instanceof Throwable) {
                        do {
                            $value = [
                                'Type' => get_class($prev),
                                'Message' => $prev->getMessage(),
                                'Code' => $prev->getCode(),
                                'File' => $prev->getFile(),
                                'Line' => $prev->getLine(),
                                'Trace' => $prev->getTraceAsString(),
                            ];

                            $this->addFact($table, 'previous Throwable', $value);

                            $prev = $prev->getPrevious();
                        } while ($prev instanceof Throwable);
                    }

                    continue;
                }

                $this->addFact($table, $key, $value);
            }
        }

        $table->render();

        $output->writeln('');

        return $output->fetch();
    }

    /**
     * @param  array[] $records
     * @phpstan-param Record[] $records
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

    /**
     * @param mixed $value
     *
     * @throws RuntimeException if encoding fails and errors are not ignored
     */
    private function stringify($value): string
    {
        return $this->replaceNewlines($this->convertToString($value));
    }

    /**
     * @param mixed $data
     *
     * @throws RuntimeException if encoding fails and errors are not ignored
     */
    private function convertToString($data): string
    {
        if (null === $data || is_bool($data)) {
            return var_export($data, true);
        }

        if (is_scalar($data)) {
            return (string) $data;
        }

        return $this->toJson($data, true);
    }

    /**
     * @throws void
     */
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

    /**
     * @param mixed $value
     *
     * @throws RuntimeException if encoding fails and errors are not ignored
     */
    private function addFact(Table $table, string $name, $value): void
    {
        $name = trim(str_replace('_', ' ', $name));

        if (is_array($value)) {
            $rowspan = count($value);

            foreach (array_keys($value) as $number => $key) {
                if (0 === $number) {
                    $table->addRow([new TableCell($name, ['rowspan' => $rowspan, 'style' => new TableCellStyle(['align' => 'right'])]), $key, $value[$key]]);
                } else {
                    $table->addRow([$key, $value[$key]]);
                }
            }

            return;
        }

        if (!is_string($value)) {
            $value = $this->stringify($value);
        }

        $table->addRow([new TableCell($name, ['style' => new TableCellStyle(['align' => 'right'])]), new TableCell($value, ['colspan' => 2])]);
    }
}
