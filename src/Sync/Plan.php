<?php

declare(strict_types=1);

namespace Atannex\ClaudeSupport\Sync;

/** Everything a sync would do, computed up front so it can be shown before it is applied. */
final readonly class Plan
{
    /**
     * @param  list<Change>  $changes
     * @param  list<string>|null  $permissions  settings entries owned by this package afterwards; null keeps the manifest's
     * @param  bool  $uninstall  delete the manifest once applied
     */
    public function __construct(
        public array $changes,
        public ?array $permissions = null,
        public bool $uninstall = false,
    ) {}

    public function isEmpty(): bool
    {
        foreach ($this->changes as $change) {
            if ($change->action->pending()) {
                return false;
            }
        }

        return true;
    }

    /** @return list<Change> */
    public function skipped(): array
    {
        return array_values(array_filter($this->changes, fn (Change $c): bool => $c->action === Action::Skip));
    }
}
