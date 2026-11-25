<?php

declare(strict_types=1);

namespace Atannex\ClaudeSupport\Console;

use Atannex\ClaudeSupport\ClaudeSupport;
use Atannex\ClaudeSupport\Exceptions\ClaudeSupportException;
use Illuminate\Console\Command;

final class UninstallCommand extends Command
{
    use RendersPlans;

    protected $signature = 'claude:uninstall
        {--force : Also delete managed files that were edited locally}
        {--dry-run : Show what would be removed without deleting anything}';

    protected $description = 'Remove everything claude-support added, keeping files and settings you edited yourself';

    public function handle(ClaudeSupport $claude): int
    {
        try {
            $plan = $claude->uninstallPlan((bool) $this->option('force'));

            $this->renderPlan($plan, applied: false);

            if ($plan->isEmpty()) {
                return self::SUCCESS;
            }

            if ($this->option('dry-run')) {
                $this->components->info('Dry run: nothing was removed.');

                return self::SUCCESS;
            }

            if (! $this->confirm('Apply the changes above?', true)) {
                return self::FAILURE;
            }

            $claude->apply($plan);
        } catch (ClaudeSupportException $e) {
            $this->components->error($e->getMessage());

            return self::FAILURE;
        }

        $this->components->info('Claude Code support removed.');

        return self::SUCCESS;
    }
}
