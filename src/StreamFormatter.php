<?php declare(strict_types=1);

namespace Mimmi20\Monolog\Formatter;

use Monolog\Formatter\NormalizerFormatter;
use Monolog\Utils;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * @phpstan-import-type Record from \Monolog\Logger
 */
class StreamFormatter extends NormalizerFormatter
{
    private const SIMPLE_FORMAT = "[%datetime%] %channel%.%level_name%: %message%";

    private bool $allowInlineLineBreaks;
    private bool $includeStacktraces;

    /**
     * @param string|null $dateFormat                 The format of the timestamp: one supported by DateTime::format
     * @param bool        $allowInlineLineBreaks      Whether to allow inline line breaks in log entries
     */
    public function __construct(?string $dateFormat = null, bool $allowInlineLineBreaks = false, bool $includeStacktraces = false)
    {
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

        $message = self::SIMPLE_FORMAT;

        foreach ($vars as $var => $val) {
            if (false !== strpos($message, '%'.$var.'%')) {
                $message = str_replace('%'.$var.'%', $this->stringify($val), $message);
            }
        }

        $output = new BufferedOutput();
        $output->writeln($message);
        $output->writeln('');

        $table = new Table($output);
        $table->setHeaders(['Title', '']);

        foreach (array('extra', 'context') as $element) {
            if (empty($record[$element])) {
                continue;
            }

            $table->addRow(['Time', $record['datetime']->format($this->dateFormat)]);
            $table->addRow(['Level', $record['level_name']]);

            foreach($record[$element] as $key => $value){
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

        if (!is_string($value)) {
            $value = $this->stringify($value);
        }

        $return = !empty($value) ? substr($value, 0, 1000) : '';

        $table->addRow([ucfirst($name), $return]);
    }
}
