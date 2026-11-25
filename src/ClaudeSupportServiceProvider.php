<?php

declare(strict_types=1);

namespace Atannex\ClaudeSupport;

use Atannex\ClaudeSupport\Console\InstallCommand;
use Atannex\ClaudeSupport\Console\StatusCommand;
use Atannex\ClaudeSupport\Console\UninstallCommand;
use Atannex\ClaudeSupport\Guidelines\GuidelinesRenderer;
use Atannex\ClaudeSupport\Resources\ResourceRepository;
use Atannex\ClaudeSupport\Stack\StackDetector;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;

final class ClaudeSupportServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/claude-support.php', 'claude-support');

        $this->app->singleton(ClaudeSupport::class, fn ($app): ClaudeSupport => new ClaudeSupport(
            $app['config'],
            $app->make(Filesystem::class),
            new StackDetector($app->make(Filesystem::class)),
            new GuidelinesRenderer,
            new ResourceRepository($app->make(Filesystem::class)),
            $app->basePath(),
        ));
    }

    public function boot(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/claude-support.php' => config_path('claude-support.php'),
        ], 'claude-support-config');

        // Copies everything shipped into resources/claude/ for you to edit;
        // your copies then take precedence (and stop following upstream).
        $this->publishes([
            ClaudeSupport::SHIPPED => resource_path('claude'),
        ], 'claude-support-resources');

        $this->commands([InstallCommand::class, StatusCommand::class, UninstallCommand::class]);
    }
}
