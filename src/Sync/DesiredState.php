<?php

declare(strict_types=1);

namespace Atannex\ClaudeSupport\Sync;

/** What the project should look like after a sync. Uninstall is the empty state. */
final readonly class DesiredState
{
    /**
     * @param  array<string, string>  $files  relative path => contents, tracked by hash
     * @param  string|null  $guidelines  body of the CLAUDE.md block; null removes the block
     * @param  list<string>  $permissions  settings.json permissions.allow entries we want present
     */
    public function __construct(
        public array $files,
        public string $guidelinesFile,
        public ?string $guidelines,
        public string $settingsFile,
        public array $permissions,
        public bool $uninstall = false,
    ) {}

    public static function empty(string $guidelinesFile, string $settingsFile): self
    {
        return new self([], $guidelinesFile, null, $settingsFile, [], uninstall: true);
    }
}
