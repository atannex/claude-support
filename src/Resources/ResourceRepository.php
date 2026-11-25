<?php

declare(strict_types=1);

namespace Atannex\ClaudeSupport\Resources;

use Atannex\ClaudeSupport\Exceptions\ClaudeSupportException;
use Illuminate\Filesystem\Filesystem;

/**
 * Discovers commands, agents and skills across source directories, applies
 * config toggles and validates each one before anything is written.
 *
 * Sources are given lowest precedence first; a later source replaces an
 * earlier resource of the same type and name. That is how a project
 * overrides what this package or another package ships.
 */
final class ResourceRepository
{
    private const NAME = '/^[a-z0-9][a-z0-9-]*$/';

    public function __construct(private readonly Filesystem $files) {}

    /**
     * @param  array<string, string>  $sources  directory => origin label, lowest precedence first
     * @param  array<string, array<string, string>>  $singles  type value => [name => absolute file], applied after the source of the same origin
     * @param  array<string, array<string, mixed>>  $toggles  type value => [name => bool]
     * @return list<ResourceDefinition> sorted by type, then name
     */
    public function resolve(array $sources, array $singles, array $toggles): array
    {
        $found = [];

        foreach ($sources as $directory => $origin) {
            foreach ($this->discover($directory, $origin) as $resource) {
                $found[$resource->type->value][$resource->name] = $resource;
            }
        }

        foreach ($singles as $type => $files) {
            foreach ($files as $name => $path) {
                $found[$type][$name] = $this->load(ResourceType::from($type), $name, [ResourceType::from($type)->entryFile($name) => $path], 'registered');
            }
        }

        $resolved = [];
        foreach (ResourceType::cases() as $type) {
            $typeToggles = (array) ($toggles[$type->value] ?? []);

            foreach ($typeToggles as $name => $enabled) {
                if ($enabled && ! isset($found[$type->value][$name])) {
                    throw ClaudeSupportException::resourceNotFound($type->label(), (string) $name);
                }
            }

            $resources = $found[$type->value] ?? [];
            ksort($resources);

            foreach ($resources as $name => $resource) {
                if (($typeToggles[$name] ?? true) !== false) {
                    $resolved[] = $resource;
                }
            }
        }

        return $resolved;
    }

    /** @return list<ResourceDefinition> */
    private function discover(string $directory, string $origin): array
    {
        $directory = rtrim($directory, '/\\');
        if (! $this->files->isDirectory($directory)) {
            return [];
        }

        $resources = [];

        foreach ([ResourceType::Command, ResourceType::Agent] as $type) {
            foreach ($this->files->glob("{$directory}/{$type->value}/*.md") ?: [] as $path) {
                $name = basename($path, '.md');
                $resources[] = $this->load($type, $name, ["{$name}.md" => $path], $origin);
            }
        }

        $skills = $this->files->isDirectory("{$directory}/skills") ? $this->files->directories("{$directory}/skills") : [];
        foreach ($skills as $skill) {
            $files = [];
            foreach ($this->files->allFiles($skill, true) as $file) {
                $files[str_replace('\\', '/', $file->getRelativePathname())] = $file->getPathname();
            }
            ksort($files);
            $resources[] = $this->load(ResourceType::Skill, basename($skill), $files, $origin);
        }

        return $resources;
    }

    /** @param  array<string, string>  $files */
    private function load(ResourceType $type, string $name, array $files, string $origin): ResourceDefinition
    {
        if (preg_match(self::NAME, $name) !== 1) {
            throw ClaudeSupportException::invalidResource($type->label(), $name, 'names may only use lowercase letters, digits and dashes');
        }

        $entry = $type->entryFile($name);
        if (! isset($files[$entry]) || ! $this->files->isFile($files[$entry])) {
            throw ClaudeSupportException::invalidResource($type->label(), $name, "{$entry} is missing");
        }

        foreach (array_keys($files) as $relative) {
            if (in_array('..', explode('/', $relative), true)) {
                throw ClaudeSupportException::unsafePath($relative);
            }
        }

        $meta = FrontMatter::parse((string) $this->files->get($files[$entry]));

        foreach ($type->requiredFrontMatter() as $key) {
            if (($meta[$key] ?? '') === '') {
                throw ClaudeSupportException::invalidResource($type->label(), $name, "front matter needs a `{$key}:` line (from {$origin})");
            }
        }

        if (isset($meta['name']) && $type !== ResourceType::Command && $meta['name'] !== $name) {
            throw ClaudeSupportException::invalidResource($type->label(), $name, "front matter name is `{$meta['name']}`; it must match the file or directory name");
        }

        return new ResourceDefinition($type, $name, $meta['description'] ?? '', $files, $origin);
    }
}
