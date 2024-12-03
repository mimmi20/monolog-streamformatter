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

final class ConfigProvider
{
    /**
     * Return general-purpose laminas-navigation configuration.
     *
     * @return array<string, array<string, array<string, string>>>
     * @phpstan-return array{monolog_formatters: array{aliases: array<string|class-string, class-string>, factories: array<class-string, class-string>}}
     *
     * @throws void
     */
    public function __invoke(): array
    {
        return [
            'monolog_formatters' => $this->getMonologFormatterConfig(),
        ];
    }

    /**
     * @return array<string, array<int|string, string>>
     * @phpstan-return array{aliases: array<string|class-string, class-string>, factories: array<class-string, class-string>}
     *
     * @throws void
     */
    public function getMonologFormatterConfig(): array
    {
        return [
            'aliases' => [
                'stream' => StreamFormatter::class,
            ],
            'factories' => [
                StreamFormatter::class => StreamFormatterFactory::class,
            ],
        ];
    }
}
