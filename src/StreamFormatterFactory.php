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

namespace Mimmi20\Monolog\Formatter;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Override;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\BufferedOutput;

use function array_key_exists;
use function is_array;

final class StreamFormatterFactory implements FactoryInterface
{
    /** @api */
    public const int DEFAULT_NORMALIZER_DEPTH = 9;

    /** @api */
    public const int DEFAULT_NORMALIZER_ITEM_COUNT = 1000;

    /**
     * @param string                                $requestedName
     * @param array<string, (bool|int|string)>|null $options
     * @phpstan-param array{format?: string, tableStyle?: string, dateFormat?: string, allowInlineLineBreaks?: bool, includeStacktraces?: bool, maxNormalizeDepth?: int, maxNormalizeItemCount?: int, prettyPrint?: bool}|null $options
     *
     * @throws void
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     */
    #[Override]
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        array | null $options = null,
    ): StreamFormatter {
        $format                = null;
        $tableStyle            = StreamFormatter::BOX_STYLE;
        $dateFormat            = null;
        $allowInlineLineBreaks = false;
        $maxNormalizeDepth     = self::DEFAULT_NORMALIZER_DEPTH;
        $maxNormalizeItemCount = self::DEFAULT_NORMALIZER_ITEM_COUNT;
        $prettyPrint           = false;
        $includeStacktraces    = false;

        if (is_array($options)) {
            if (array_key_exists('format', $options)) {
                $format = $options['format'];
            }

            if (array_key_exists('tableStyle', $options)) {
                $tableStyle = $options['tableStyle'];
            }

            if (array_key_exists('dateFormat', $options)) {
                $dateFormat = $options['dateFormat'];
            }

            if (array_key_exists('allowInlineLineBreaks', $options)) {
                $allowInlineLineBreaks = $options['allowInlineLineBreaks'];
            }

            if (array_key_exists('maxNormalizeDepth', $options)) {
                $maxNormalizeDepth = $options['maxNormalizeDepth'];
            }

            if (array_key_exists('maxNormalizeItemCount', $options)) {
                $maxNormalizeItemCount = $options['maxNormalizeItemCount'];
            }

            if (array_key_exists('prettyPrint', $options)) {
                $prettyPrint = $options['prettyPrint'];
            }

            if (array_key_exists('includeStacktraces', $options)) {
                $includeStacktraces = $options['includeStacktraces'];
            }
        }

        $output = new BufferedOutput();

        $formatter = new StreamFormatter(
            $output,
            new Table($output),
            $format,
            $tableStyle,
            $dateFormat,
            $allowInlineLineBreaks,
            $includeStacktraces,
        );

        $formatter->setMaxNormalizeDepth($maxNormalizeDepth);
        $formatter->setMaxNormalizeItemCount($maxNormalizeItemCount);
        $formatter->setJsonPrettyPrint($prettyPrint);

        return $formatter;
    }
}
