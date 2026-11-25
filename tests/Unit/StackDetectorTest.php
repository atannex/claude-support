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
        $this->writeFile('package.json', json_encode([
            'dependencies' => ['vue' => '^3', '@inertiajs/vue3' => '^2', '@radix-ui/react-slot' => '^1'],
            'devDependencies' => ['vite' => '^6', 'tailwindcss' => '^4', 'angular' => '^1'],
        ]));

        $stack = (new StackDetector(new Filesystem))->detect($this->root, 'pgsql');

        $this->assertSame('^8.3', $stack->php);
        $this->assertSame('12.4.1', $stack->laravel);
        $this->assertSame('Pest', $stack->testRunner);
        $this->assertTrue($stack->pint);
        $this->assertTrue($stack->phpstan);
        $this->assertSame(['Livewire' => '3.6.0', 'atannex/billing' => '^1.0'], $stack->packages);
        // "angular" is the unrelated legacy package; Angular is @angular/core, so it must not match.
        $this->assertSame(['Vue', 'Inertia (Vue)', 'Tailwind CSS', 'Radix UI', 'Vite'], $stack->frontend);
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

    public function test_malformed_manifests_do_not_crash_detection(): void
    {
        $this->writeFile('composer.json', json_encode([
            'require' => ['php' => 8.2, 'laravel/framework' => ['^12.0'], 'laravel/pint' => '^1.0'],
            'require-dev' => 'oops',
            'scripts' => 'nope',
        ]));
        $this->writeFile('composer.lock', json_encode(['packages' => ['not-a-package', ['name' => 'x/y', 'version' => ['1']]], 'packages-dev' => 5]));
        $this->writeFile('package.json', json_encode(['dependencies' => 'vue', 'devDependencies' => ['vite' => '^6']]));

        $stack = (new StackDetector(new Filesystem))->detect($this->root);

        $this->assertNull($stack->php);
        $this->assertNull($stack->laravel);
        $this->assertTrue($stack->pint);
        $this->assertSame(['Vite'], $stack->frontend);
        $this->assertSame([], $stack->composerScripts);
    }
}
