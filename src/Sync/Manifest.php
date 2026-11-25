<?php

declare(strict_types=1);

namespace Atannex\ClaudeSupport\Sync;

use Atannex\ClaudeSupport\Exceptions\ClaudeSupportException;
use Illuminate\Filesystem\Filesystem;

/**
 * The record of what this package wrote: a sha256 per file and the settings
 * permissions it added. It is what lets a sync tell "outdated" from "edited
 * by you", and lets uninstall remove only its own work. Commit it.
 */
final class Manifest
{
    public const PATH = '.claude/claude-support.json';

    private const SCHEMA = 1;

    /**
     * @param  array<string, string>  $files  relative path => sha256
     * @param  list<string>  $permissions
     */
    private function __construct(
        private array $files = [],
        private array $permissions = [],
    ) {}

    public static function load(Filesystem $fs, string $root): self
    {
        $path = $root.'/'.self::PATH;
        if (! $fs->isFile($path)) {
            return new self;
        }

        $data = json_decode((string) $fs->get($path), true);
        if (! is_array($data) || ! is_array($data['files'] ?? []) || ! is_array($data['permissions'] ?? [])) {
            throw ClaudeSupportException::unreadableManifest($path);
        }

        return new self(
            array_map('strval', $data['files'] ?? []),
            array_values(array_map('strval', $data['permissions'] ?? [])),
        );
    }

    public function save(Filesystem $fs, string $root): void
    {
        ksort($this->files);

        $fs->ensureDirectoryExists(dirname($root.'/'.self::PATH));
        $fs->put($root.'/'.self::PATH, json_encode([
            'generator' => 'atannex/claude-support',
            'schema' => self::SCHEMA,
            'files' => (object) $this->files,
            'permissions' => $this->permissions,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n");
    }

    /**
     * Hash of the content as git sees it. Line endings are normalised so a
     * checkout with core.autocrlf (the Windows default) still counts as
     * unchanged; binary files are hashed byte for byte.
     */
    public static function hash(string $contents): string
    {
        return hash('sha256', self::normalize($contents));
    }

    public static function same(string $a, string $b): bool
    {
        return self::normalize($a) === self::normalize($b);
    }

    private static function normalize(string $contents): string
    {
        return str_contains($contents, "\0") ? $contents : str_replace("\r\n", "\n", $contents);
    }

    public function hashOf(string $path): ?string
    {
        return $this->files[$path] ?? null;
    }

    /** @return list<string> */
    public function paths(): array
    {
        return array_keys($this->files);
    }

    public function record(string $path, string $contents): void
    {
        $this->files[$path] = self::hash($contents);
    }

    public function forget(string $path): void
    {
        unset($this->files[$path]);
    }

    /** @return list<string> */
    public function permissions(): array
    {
        return $this->permissions;
    }

    /** @param  list<string>  $permissions */
    public function setPermissions(array $permissions): void
    {
        $this->permissions = $permissions;
    }
}
