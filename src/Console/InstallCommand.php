<?php

declare(strict_types=1);

namespace Atannex\ClaudeSupport\Console;

use Atannex\ClaudeSupport\ClaudeSupport;
use Atannex\ClaudeSupport\Exceptions\ClaudeSupportException;
use Illuminate\Console\Command;

final class InstallCommand extends Command
{
    use RendersPlans;

    protected $signature = 'claude:install
        {--force : Replace files that were edited locally or not written by this package}
        {--dry-run : Show what would change without writing anything}';

    protected $description = 'Install or refresh Claude Code commands, CLAUDE.md guidelines and settings for this project';

    public function handle(ClaudeSupport $claude): int
    {
        try {
            $plan = $claude->plan((bool) $this->option('force'));
            $dryRun = (bool) $this->option('dry-run');

            if (! $dryRun) {
                $claude->apply($plan);
            }
        } catch (ClaudeSupportException $e) {
            $this->components->error($e->getMessage());

            return self::FAILURE;
        }

        $this->renderPlan($plan, applied: ! $dryRun);

        if ($dryRun) {
            $this->components->info('Dry run: nothing was written.');
        } elseif (! $plan->isEmpty()) {
            $this->components->info('Claude Code support is in sync. Commit .claude/ and CLAUDE.md so the whole team shares it.');
        }

        return self::SUCCESS;
    }
}
