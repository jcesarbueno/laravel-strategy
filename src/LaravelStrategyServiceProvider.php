<?php

declare(strict_types=1);

namespace Jcesarbueno\LaravelStrategy;

use Illuminate\Support\ServiceProvider;
use Jcesarbueno\LaravelStrategy\Commands\MakeStrategyCommand;

final class LaravelStrategyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->commands([
            MakeStrategyCommand::class,
        ]);
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../stubs' => base_path('stubs/laravel-strategy'),
        ], 'stubs');
    }
}
