<?php

declare(strict_types=1);

namespace Atannex\ClaudeSupport;

use Atannex\ClaudeSupport\Exceptions\ClaudeSupportException;
use Atannex\ClaudeSupport\Guidelines\GuidelinesRenderer;
use Atannex\ClaudeSupport\Resources\ResourceDefinition;
use Atannex\ClaudeSupport\Resources\ResourceRepository;
use Atannex\ClaudeSupport\Resources\ResourceType;
use Atannex\ClaudeSupport\Stack\Stack;
use Atannex\ClaudeSupport\Stack\StackDetector;
use Atannex\ClaudeSupport\Sync\DesiredState;
use Atannex\ClaudeSupport\Sync\Manifest;
use Atannex\ClaudeSupport\Sync\Plan;
use Atannex\ClaudeSupport\Sync\Synchronizer;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Filesystem\Filesystem;

/**
 * Entry point: resolves which commands, agents, skills, guidelines and
 * permissions a project should have, and plans or applies syncing them.
 *
 * Other packages contribute from their service provider's boot():
 *
 *     ClaudeSupport::source(__DIR__.'/../resources/claude');      // commands/, agents/, skills/
 *     ClaudeSupport::command('billing-audit', __DIR__.'/../billing-audit.md');
 */
final class ClaudeSupport
{
    public const SHIPPED = __DIR__.'/../resources/claude';

    /** @var list<string> package source directories, in registration order */
    private array $sources = [];

    /** @var array<string, array<string, string>> type value => [name => file] */
    private array $singles = [];

    public function __construct(
        private readonly Repository $config,
        private readonly Filesystem $files,
        private readonly StackDetector $detector,
        private readonly GuidelinesRenderer $renderer,
        private readonly ResourceRepository $repository,
        private readonly string $basePath,
    ) {}

    /** Register a directory laid out like .claude/ (commands/, agents/, skills/). */
    public function source(string $directory): self
    {
        $this->sources[] = $directory;

        return $this;
    }

    public function command(string $name, string $path): self
    {
        $this->singles[ResourceType::Command->value][$name] = $path;

        return $this;
    }

    public function agent(string $name, string $path): self
    {
        $this->singles[ResourceType::Agent->value][$name] = $path;

        return $this;
    }

    /**
     * Everything that will be installed, after toggles and overrides.
     * Precedence, lowest first: this package, other packages, the project's own sources.
     *
     * @return list<ResourceDefinition>
     */
    public function resources(): array
    {
        $sources = [self::SHIPPED => 'atannex/claude-support'];

        foreach ($this->sources as $directory) {
            $sources[$directory] = 'package';
        }

        foreach ((array) $this->config->get('claude-support.sources', []) as $directory) {
            if (is_string($directory) && $directory !== '') {
                $sources[$directory] = 'project';
            }
        }

        $toggles = [];
        foreach (ResourceType::cases() as $type) {
            $toggles[$type->value] = (array) $this->config->get("claude-support.{$type->value}", []);
        }

        return $this->repository->resolve($sources, $this->singles, $toggles);
    }

    public function root(): string
    {
        $path = $this->config->get('claude-support.path');

        return rtrim(is_string($path) && $path !== '' ? $path : $this->basePath, '/\\');
    }

    public function stack(): Stack
    {
        $connection = $this->config->get('database.default');
        $driver = is_string($connection) ? $this->config->get("database.connections.{$connection}.driver") : null;

        return $this->detector->detect($this->root(), is_string($driver) ? $driver : null);
    }

    public function desired(): DesiredState
    {
        $resources = $this->resources();
        $files = [];

        foreach ($resources as $resource) {
            foreach ($resource->files as $relative => $source) {
                $files[$resource->type->target($resource->name, $relative)] = (string) $this->files->get($source);
            }
        }

        $stack = $this->stack();
        $guidelines = null;

        if ($this->config->get('claude-support.guidelines.enabled', true)) {
            $guidelines = $this->renderer->render(
                $stack,
                $resources,
                $this->strings('claude-support.guidelines.conventions'),
                $this->nullableString('claude-support.guidelines.language'),
            );
        }

        $permissions = $this->config->get('claude-support.settings.enabled', true) ? $this->permissions($stack) : [];

        return new DesiredState($files, $this->guidelinesFile(), $guidelines, $this->settingsFile(), $permissions);
    }

    public function plan(bool $force = false): Plan
    {
        return $this->synchronizer()->plan($this->desired(), $this->manifest(), $force);
    }

    public function uninstallPlan(bool $force = false): Plan
    {
        return $this->synchronizer()->plan(DesiredState::empty($this->guidelinesFile(), $this->settingsFile()), $this->manifest(), $force);
    }

    public function apply(Plan $plan): void
    {
        $this->synchronizer()->apply($plan, $this->manifest());
    }

    /** @return list<string> */
    private function permissions(Stack $stack): array
    {
        $permissions = ['Bash(php artisan test:*)'];

        if ($stack->hasComposerScript('test')) {
            $permissions[] = 'Bash(composer test:*)';
        }
        if ($stack->pint) {
            $permissions[] = 'Bash(vendor/bin/pint:*)';
        }
        if ($stack->phpstan) {
            $permissions[] = 'Bash(vendor/bin/phpstan:*)';
        }

        return array_values(array_unique([...$permissions, ...$this->strings('claude-support.settings.permissions')]));
    }

    /** @return list<string> */
    private function strings(string $key): array
    {
        return array_values(array_filter((array) $this->config->get($key, []), fn ($v): bool => is_string($v) && trim($v) !== ''));
    }

    private function nullableString(string $key): ?string
    {
        $value = $this->config->get($key);

        return is_string($value) && trim($value) !== '' ? trim($value) : null;
    }

    private function guidelinesFile(): string
    {
        return $this->relative((string) $this->config->get('claude-support.guidelines.file', 'CLAUDE.md'));
    }

    private function settingsFile(): string
    {
        return $this->relative((string) $this->config->get('claude-support.settings.file', '.claude/settings.json'));
    }

    private function relative(string $path): string
    {
        $path = ltrim(str_replace('\\', '/', $path), '/');

        if ($path === '' || preg_match('/^[A-Za-z]:/', $path) === 1 || in_array('..', explode('/', $path), true)) {
            throw ClaudeSupportException::unsafePath($path);
        }

        return $path;
    }

    private function manifest(): Manifest
    {
        return Manifest::load($this->files, $this->root());
    }

    private function synchronizer(): Synchronizer
    {
        return new Synchronizer($this->files, $this->root());
    }
}
