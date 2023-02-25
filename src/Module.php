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

use Laminas\ModuleManager\Feature\ConfigProviderInterface;

final class Module implements ConfigProviderInterface
{
    /**
     * Return default configuration for laminas-mvc applications.
     *
     * @return array<string, array<string, array<string, string>>>
     * @phpstan-return array{monolog_formatters: array{aliases: array<string|class-string, class-string>, factories: array<class-string, class-string>}}
     *
     * @throws void
     */
    public function getConfig(): array
    {
        $provider = new ConfigProvider();

        return [
            'monolog_formatters' => $provider->getMonologFormatterConfig(),
        ];
    }
}
