<?php

declare(strict_types=1);

namespace Atannex\ClaudeSupport\Exceptions;

use RuntimeException;

final class ClaudeSupportException extends RuntimeException
{
    public static function invalidResource(string $type, string $name, string $reason): self
    {
        return new self("Invalid {$type} [{$name}]: {$reason}.");
    }

    public static function resourceNotFound(string $type, string $name): self
    {
        return new self("The {$type} [{$name}] is enabled in config/claude-support.php but no source provides it.");
    }

    public static function unsafePath(string $path): self
    {
        return new self("Path [{$path}] must be relative to the project root and must not contain '..'.");
    }

    public static function unreadableManifest(string $path): self
    {
        return new self("Manifest [{$path}] is not valid JSON; fix or delete it before syncing.");
    }
}
