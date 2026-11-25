<?php

declare(strict_types=1);

namespace Atannex\ClaudeSupport\Console;

use Atannex\ClaudeSupport\Sync\Action;
use Atannex\ClaudeSupport\Sync\Change;
use Atannex\ClaudeSupport\Sync\Plan;
use Illuminate\Console\Command;

/** @mixin Command */
trait RendersPlans
{
    private function renderPlan(Plan $plan, bool $applied): void
    {
        if ($plan->changes === []) {
            $this->components->info('Nothing to do.');

            return;
        }

        foreach ($plan->changes as $change) {
            $this->components->twoColumnDetail(
                $change->path.($change->reason !== null ? " <fg=gray>— {$change->reason}</>" : ''),
                $this->verb($change, $applied),
            );
        }

        $this->newLine();

        if ($plan->skipped() !== []) {
            $this->components->warn(count($plan->skipped()).' file(s) left untouched to protect local changes.');
        }
    }

    private function verb(Change $change, bool $applied): string
    {
        [$label, $color] = match ($change->action) {
            Action::Create => [$applied ? 'created' : 'will create', 'green'],
            Action::Update => [$applied ? 'updated' : 'will update', 'green'],
            Action::Overwrite => [$applied ? 'overwritten' : 'will overwrite', 'yellow'],
            Action::Adopt => [$applied ? 'tracked' : 'will track', 'blue'],
            Action::Unchanged => ['up to date', 'gray'],
            Action::Skip => ['skipped', 'yellow'],
            Action::Remove => [$applied ? 'removed' : 'will remove', 'red'],
            Action::Release => [$applied ? 'released' : 'will release', 'gray'],
        };

        return "<fg={$color};options=bold>".strtoupper($label).'</>';
    }
}
