<?php

declare(strict_types=1);

namespace Jcesarbueno\LaravelStrategy\Tests;

use Jcesarbueno\LaravelStrategy\LaravelStrategyServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            LaravelStrategyServiceProvider::class,
        ];
    }
}
