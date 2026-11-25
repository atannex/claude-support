<?php

declare(strict_types=1);

namespace Atannex\ClaudeSupport\Sync;

final readonly class Change
{
    /**
     * @param  string  $path  relative to the project root
     * @param  string|null  $contents  the full file to write, for writing actions
     * @param  bool  $tracked  whether the file's hash is kept in the manifest
     */
    public function __construct(
        public string $path,
        public Action $action,
        public ?string $contents = null,
        public ?string $reason = null,
        public bool $tracked = true,
    ) {}
}
