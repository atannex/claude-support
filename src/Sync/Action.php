<?php

declare(strict_types=1);

namespace Atannex\ClaudeSupport\Sync;

enum Action: string
{
    /** The file does not exist yet and will be written. */
    case Create = 'create';
    /** Managed, untouched locally, and the package has a newer version. */
    case Update = 'update';
    /** Edited locally or not ours, replaced because --force was given. */
    case Overwrite = 'overwrite';
    /** Already identical to what we ship; start tracking it. */
    case Adopt = 'adopt';
    case Unchanged = 'unchanged';
    /** Left alone to protect local work; see the change's reason. */
    case Skip = 'skip';
    case Remove = 'remove';
    /** Stop tracking without touching the file (it is gone, or kept on purpose). */
    case Release = 'release';

    public function writes(): bool
    {
        return in_array($this, [self::Create, self::Update, self::Overwrite], true);
    }

    /** True when applying the plan would change something on disk or in the manifest. */
    public function pending(): bool
    {
        return $this !== self::Unchanged;
    }

    /** How the file stands today, for claude:status. */
    public function state(): string
    {
        return match ($this) {
            self::Create => 'missing',
            self::Update => 'outdated',
            self::Overwrite, self::Skip => 'diverged',
            self::Adopt => 'untracked, identical',
            self::Unchanged => 'up to date',
            self::Remove, self::Release => 'no longer shipped',
        };
    }
}
