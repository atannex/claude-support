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

        $files = [];
        foreach ($data['files'] ?? [] as $file => $hash) {
            // The manifest is committed, so treat it as untrusted: a crafted path would
            // otherwise let a sync or uninstall delete files outside .claude/.
            if (! is_string($file) || ! self::isManagedPath($file) || ! is_string($hash)) {
                throw ClaudeSupportException::unreadableManifest($path, 'entry ['.$file.'] is not a file under .claude/ with a sha256');
            }
            $files[$file] = $hash;
        }

        return new self(
            $files,
            array_values(array_filter($data['permissions'] ?? [], 'is_string')),
        );
    }

    /** Only resource files under .claude/ are ever tracked; anything else in the manifest is tampering. */
    public static function isManagedPath(string $path): bool
    {
        return str_starts_with($path, '.claude/')
            && $path !== self::PATH
            && ! str_contains($path, '\\')
            && ! str_contains($path, "\0")
            && ! in_array('..', explode('/', $path), true);
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
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)."\n");
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
