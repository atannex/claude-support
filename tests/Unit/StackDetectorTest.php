<?php

declare(strict_types=1);

namespace Atannex\ClaudeSupport\Tests\Unit;

use Atannex\ClaudeSupport\Stack\StackDetector;
use Atannex\ClaudeSupport\Tests\TestCase;
use Illuminate\Filesystem\Filesystem;

final class StackDetectorTest extends TestCase
{
    public function test_it_reads_versions_from_the_lock_file_and_tooling_from_manifests(): void
    {
        $this->writeFile('composer.json', json_encode([
            'require' => ['php' => '^8.3', 'laravel/framework' => '^12.0', 'livewire/livewire' => '^3.0', 'atannex/billing' => '^1.0'],
            'require-dev' => ['pestphp/pest' => '^3.0', 'laravel/pint' => '^1.0', 'larastan/larastan' => '^3.0'],
            'scripts' => ['test' => 'pest'],
        ]));
        $this->writeFile('composer.lock', json_encode([
            'packages' => [['name' => 'laravel/framework', 'version' => 'v12.4.1'], ['name' => 'livewire/livewire', 'version' => 'v3.6.0']],
            'packages-dev' => [],
        ]));
        $this->writeFile('package.json', json_encode(['devDependencies' => ['vite' => '^6', 'tailwindcss' => '^4', 'vue' => '^3']]));

        $stack = (new StackDetector(new Filesystem))->detect($this->root, 'pgsql');

        $this->assertSame('^8.3', $stack->php);
        $this->assertSame('12.4.1', $stack->laravel);
        $this->assertSame('Pest', $stack->testRunner);
        $this->assertTrue($stack->pint);
        $this->assertTrue($stack->phpstan);
        $this->assertSame(['Livewire' => '3.6.0', 'atannex/billing' => '^1.0'], $stack->packages);
        $this->assertSame(['Vue', 'Tailwind CSS', 'Vite'], $stack->frontend);
        $this->assertTrue($stack->hasComposerScript('test'));
        $this->assertSame('pgsql', $stack->database);
    }

    public function test_missing_or_broken_manifests_yield_defaults(): void
    {
        $this->writeFile('composer.json', '{not json');

        $stack = (new StackDetector(new Filesystem))->detect($this->root);

        $this->assertNull($stack->laravel);
        $this->assertSame('PHPUnit', $stack->testRunner);
        $this->assertSame([], $stack->packages);
    }
}
