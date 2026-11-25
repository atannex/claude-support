<?php

declare(strict_types=1);

namespace Atannex\ClaudeSupport\Console;

use Atannex\ClaudeSupport\ClaudeSupport;
use Atannex\ClaudeSupport\Exceptions\ClaudeSupportException;
use Illuminate\Console\Command;

final class StatusCommand extends Command
{
    protected $signature = 'claude:status
        {--check : Exit with a failure code when anything is out of sync (for CI)}
        {--json : Print machine-readable output}';

    protected $description = 'Show whether Claude Code support files are up to date, outdated or edited locally';

    public function handle(ClaudeSupport $claude): int
    {
        try {
            $plan = $claude->plan();
        } catch (ClaudeSupportException $e) {
            $this->components->error($e->getMessage());

            return self::FAILURE;
        }

        if ($this->option('json')) {
            $this->line((string) json_encode([
                'in_sync' => $plan->isEmpty(),
                'files' => array_map(fn ($change): array => [
                    'path' => $change->path,
                    'state' => $change->action->state(),
                    'action' => $change->action->value,
                    'reason' => $change->reason,
                ], $plan->changes),
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return $plan->isEmpty() || ! $this->option('check') ? self::SUCCESS : self::FAILURE;
        }

        foreach ($plan->changes as $change) {
            $state = $change->action->state();
            $color = $change->action->pending() ? 'yellow' : 'green';

            $this->components->twoColumnDetail(
                $change->path.($change->reason !== null ? " <fg=gray>— {$change->reason}</>" : ''),
                "<fg={$color}>{$state}</>",
            );
        }

        $this->newLine();

        if ($plan->isEmpty()) {
            $this->components->info('Everything is in sync.');

            return self::SUCCESS;
        }

        $this->components->warn('Out of sync. Run `php artisan claude:install --dry-run` to preview the changes.');

        return $this->option('check') ? self::FAILURE : self::SUCCESS;
    }
}
