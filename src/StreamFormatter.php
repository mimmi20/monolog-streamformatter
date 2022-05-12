<?php declare(strict_types=1);

namespace Mimmi20\Monolog\Formatter;

use Monolog\Formatter\NormalizerFormatter;
use Monolog\Utils;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * @phpstan-import-type Record from \Monolog\Logger
 */
class StreamFormatter extends NormalizerFormatter
{
    private const SIMPLE_FORMAT = "%message%";

    private string $format;
    private string $tableStyle;
    private bool $allowInlineLineBreaks;
    private bool $includeStacktraces;

    /**
     * @param string|null $format                     The format of the message
     * @param string|null $dateFormat                 The format of the timestamp: one supported by DateTime::format
     * @param bool        $allowInlineLineBreaks      Whether to allow inline line breaks in log entries
     */
    public function __construct(?string $format = null, string $tableStyle = 'box', ?string $dateFormat = null, bool $allowInlineLineBreaks = false, bool $includeStacktraces = false)
    {
        $this->format = $format === null ? self::SIMPLE_FORMAT : $format;
        $this->tableStyle = $tableStyle;
        $this->allowInlineLineBreaks = $allowInlineLineBreaks;
        $this->includeStacktraces($includeStacktraces);

        parent::__construct($dateFormat);
    }

    public function includeStacktraces(bool $include = true): self
    {
        $this->includeStacktraces = $include;

        if ($this->includeStacktraces) {
            $this->allowInlineLineBreaks = true;
        }

        return $this;
    }

    /**
     * Formats a log record.
     *
     * @param  array $record A record to format
     * @return mixed The formatted record
     *
     * @phpstan-param Record $record
     */
    public function format(array $record): string
    {
        $vars = parent::format($record);

        $message = $this->format;

        foreach ($vars['extra'] as $var => $val) {
            if (false !== strpos($message, '%extra.'.$var.'%')) {
                $message = str_replace('%extra.'.$var.'%', $this->stringify($val), $message);
                unset($vars['extra'][$var]);
            }
        }

        foreach ($vars['context'] as $var => $val) {
            if (false !== strpos($message, '%context.'.$var.'%')) {
                $message = str_replace('%context.'.$var.'%', $this->stringify($val), $message);
                unset($vars['context'][$var]);
            }
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
            if (false !== strpos($message, '%'.$var.'%')) {
                $message = str_replace('%'.$var.'%', $this->stringify($val), $message);
            }
        }

        $output = new BufferedOutput();
        $output->writeln(str_repeat('=', 220));
        $output->writeln('');
        $output->writeln($message);
        $output->writeln('');

        $table = new Table($output);
        $table->setStyle($this->tableStyle);
        $table->setColumnWidths([20, 20, 60]);
        $table->setHeaderTitle($record['level_name']);
        $table->setHeaders([new TableCell('General Info', ['colspan' => 3])]);

        $table->addRow(['Time', new TableCell($record['datetime']->format($this->dateFormat), ['colspan' => 2])]);
        $table->addRow(['Level', new TableCell($record['level_name'], ['colspan' => 2])]);

        $output->writeln('');

        foreach (array('extra', 'context') as $element) {
            if (empty($record[$element])) {
                continue;
            }

            $table->addRow(new TableSeparator());
            $table->addRow([new TableCell(ucfirst($element), ['colspan' => 3])]);
            $table->addRow(new TableSeparator());

            foreach($record[$element] as $key => $value){
                if ($value instanceof \Throwable) {
                    $exception = $value;

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

                    if ($prev instanceof \Throwable) {
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
                        } while ($prev instanceof \Throwable);
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
     */
    public function stringify($value): string
    {
        return $this->replaceNewlines($this->convertToString($value));
    }

    /**
     * @param mixed $data
     */
    protected function convertToString($data): string
    {
        if (null === $data || is_bool($data)) {
            return var_export($data, true);
        }

        if (is_scalar($data)) {
            return (string) $data;
        }

        return $this->toJson($data, true);
    }

    protected function replaceNewlines(string $str): string
    {
        if ($this->allowInlineLineBreaks) {
            if (0 === strpos($str, '{')) {
                return str_replace(array('\r', '\n'), array("\r", "\n"), $str);
            }

            return $str;
        }

        return str_replace(["\r\n", "\r", "\n"], ' ', $str);
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return void
     */
    private function addFact(Table $table, string $name, $value): void
    {
        $name = trim(str_replace('_', ' ', $name));

        if (is_array($value)) {
            $rowspan = count($value);

            foreach (array_keys($value) as $number => $key) {
                if (0 === $number) {
                    $table->addRow([new TableCell(ucfirst($name), ['rowspan' => $rowspan]), $key, $value[$key]]);
                } else {
                    $table->addRow([$key, $value[$key]]);
                }
            }

            return;
        }

        if (!is_string($value)) {
            $value = $this->stringify($value);
        }

        $table->addRow([ucfirst($name), new TableCell($value, ['colspan' => 2])]);
    }
}
