<?php

declare(strict_types=1);

namespace Atannex\ClaudeSupport\Tests;

use Atannex\ClaudeSupport\ClaudeSupportServiceProvider;
use Atannex\ClaudeSupport\Facades\ClaudeSupport;
use Illuminate\Filesystem\Filesystem;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    /** A throwaway project root per test. */
    protected string $root;

    protected function setUp(): void
    {
        $this->root = sys_get_temp_dir().'/claude-support-'.bin2hex(random_bytes(6));
        mkdir($this->root, 0777, true);

        parent::setUp();
    }

    protected function tearDown(): void
    {
        (new Filesystem)->deleteDirectory($this->root);

        parent::tearDown();
    }

    protected function getPackageProviders($app): array
    {
        return [ClaudeSupportServiceProvider::class];
    }

    protected function getPackageAliases($app): array
    {
        return ['ClaudeSupport' => ClaudeSupport::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('claude-support.path', $this->root);
        $app['config']->set('claude-support.sources', []);
    }

    protected function writeFile(string $path, string $contents): void
    {
        (new Filesystem)->ensureDirectoryExists(dirname($this->root.'/'.$path));
        file_put_contents($this->root.'/'.$path, $contents);
    }

    protected function readFile(string $path): string
    {
        return (string) file_get_contents($this->root.'/'.$path);
    }

    /** @return array<string, mixed> */
    protected function readJson(string $path): array
    {
        return json_decode($this->readFile($path), true);
    }
}
