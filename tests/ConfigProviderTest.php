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

use Mimmi20\Monolog\Formatter\ConfigProvider;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;

final class ConfigProviderTest extends TestCase
{
    /** @throws Exception */
    public function testGetMonologFormatterConfig(): void
    {
        $monologFormatterConfig = (new ConfigProvider())->getMonologFormatterConfig();
        self::assertIsArray($monologFormatterConfig);
        self::assertCount(2, $monologFormatterConfig);

        self::assertArrayNotHasKey('abstract_factories', $monologFormatterConfig);
        self::assertArrayNotHasKey('delegators', $monologFormatterConfig);
        self::assertArrayNotHasKey('initializers', $monologFormatterConfig);
        self::assertArrayNotHasKey('invokables', $monologFormatterConfig);
        self::assertArrayNotHasKey('services', $monologFormatterConfig);
        self::assertArrayNotHasKey('shared', $monologFormatterConfig);

        self::assertArrayHasKey('aliases', $monologFormatterConfig);
        $aliases = $monologFormatterConfig['aliases'];
        self::assertIsArray($aliases);
        self::assertCount(1, $aliases);

        self::assertArrayHasKey('factories', $monologFormatterConfig);
        $factories = $monologFormatterConfig['factories'];
        self::assertIsArray($factories);
        self::assertCount(1, $factories);
    }

    /** @throws Exception */
    public function testInvoke(): void
    {
        $config = (new ConfigProvider())();
        self::assertIsArray($config);
        self::assertCount(1, $config);

        self::assertArrayHasKey('monolog_formatters', $config);

        $monologFormatterConfig = $config['monolog_formatters'];
        self::assertIsArray($monologFormatterConfig);
        self::assertCount(2, $monologFormatterConfig);

        self::assertArrayNotHasKey('abstract_factories', $monologFormatterConfig);
        self::assertArrayNotHasKey('delegators', $monologFormatterConfig);
        self::assertArrayNotHasKey('initializers', $monologFormatterConfig);
        self::assertArrayNotHasKey('invokables', $monologFormatterConfig);
        self::assertArrayNotHasKey('services', $monologFormatterConfig);
        self::assertArrayNotHasKey('shared', $monologFormatterConfig);

        self::assertArrayHasKey('aliases', $monologFormatterConfig);
        $aliases = $monologFormatterConfig['aliases'];
        self::assertIsArray($aliases);
        self::assertCount(1, $aliases);

        self::assertArrayHasKey('factories', $monologFormatterConfig);
        $factories = $monologFormatterConfig['factories'];
        self::assertIsArray($factories);
        self::assertCount(1, $factories);
    }
}
