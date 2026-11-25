<?php

declare(strict_types=1);

namespace Atannex\ClaudeSupport\Stack;

use Illuminate\Filesystem\Filesystem;

/**
 * Reads composer.json, composer.lock and package.json at the project root and
 * reports what the Catalog recognises. Missing or unreadable manifests simply
 * yield fewer facts.
 */
final class StackDetector
{
    public function __construct(private readonly Filesystem $files) {}

    public function detect(string $root, ?string $database = null): Stack
    {
        $composer = $this->json($root.'/composer.json');
        $installed = $this->installedVersions($root);
        $declared = [...$this->dependencies($composer, 'require'), ...$this->dependencies($composer, 'require-dev')];
        $has = fn (string $package): bool => isset($installed[$package]) || isset($declared[$package]);
        $version = fn (string $package): ?string => $installed[$package] ?? $declared[$package] ?? null;

        $packages = [];
        foreach (Catalog::match(Catalog::COMPOSER, [...$declared, ...$installed]) as $label => $package) {
            $packages[$label] = (string) $version($package);
        }

        // atannex/* packages are first-party here: always worth naming.
        foreach (array_keys([...$declared, ...$installed]) as $package) {
            if (str_starts_with((string) $package, 'atannex/') && $package !== 'atannex/claude-support') {
                $packages[$package] = (string) $version($package);
            }
        }

        $node = $this->json($root.'/package.json');
        $nodeDeps = [...$this->dependencies($node, 'dependencies'), ...$this->dependencies($node, 'devDependencies')];
        $frontend = array_keys(Catalog::match(Catalog::NPM, $nodeDeps));

        return new Stack(
            php: $declared['php'] ?? null,
            laravel: $version('laravel/framework'),
            testRunner: $has('pestphp/pest') ? 'Pest' : 'PHPUnit',
            pint: $has('laravel/pint'),
            phpstan: $has('larastan/larastan') || $has('phpstan/phpstan') || $has('nunomaduro/larastan'),
            packages: $packages,
            frontend: $frontend,
            composerScripts: is_array($composer['scripts'] ?? null) ? array_map('strval', array_keys($composer['scripts'])) : [],
            database: $database,
        );
    }

    /** @return array<string, string> package => version, from composer.lock */
    private function installedVersions(string $root): array
    {
        $lock = $this->json($root.'/composer.lock');
        $versions = [];

        foreach (['packages', 'packages-dev'] as $section) {
            foreach (is_array($lock[$section] ?? null) ? $lock[$section] : [] as $package) {
                if (is_array($package) && is_string($package['name'] ?? null) && is_string($package['version'] ?? null)) {
                    $versions[$package['name']] = ltrim($package['version'], 'v');
                }
            }
        }

        return $versions;
    }

    /**
     * A name => constraint map from a manifest, ignoring entries that aren't strings.
     *
     * @param  array<string, mixed>  $manifest
     * @return array<string, string>
     */
    private function dependencies(array $manifest, string $key): array
    {
        $dependencies = [];

        foreach (is_array($manifest[$key] ?? null) ? $manifest[$key] : [] as $name => $constraint) {
            if (is_string($name) && is_string($constraint)) {
                $dependencies[$name] = $constraint;
            }
        }

        return $dependencies;
    }

    /** @return array<string, mixed> */
    private function json(string $path): array
    {
        if (! $this->files->isFile($path)) {
            return [];
        }

        $data = json_decode((string) $this->files->get($path), true);

        return is_array($data) ? $data : [];
    }
}
