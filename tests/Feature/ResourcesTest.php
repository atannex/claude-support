<?php

declare(strict_types=1);

namespace Atannex\ClaudeSupport\Tests\Feature;

use Atannex\ClaudeSupport\Facades\ClaudeSupport;
use Atannex\ClaudeSupport\Tests\TestCase;

final class ResourcesTest extends TestCase
{
    public function test_it_installs_every_shipped_command_agent_and_skill(): void
    {
        $this->artisan('claude:install')->assertSuccessful();

        foreach (['enterprise-redesign', 'audit', 'feature', 'upgrade-laravel'] as $command) {
            $this->assertFileExists($this->root."/.claude/commands/{$command}.md");
        }
        $this->assertFileExists($this->root.'/.claude/agents/migration-reviewer.md');
        $this->assertFileExists($this->root.'/.claude/agents/query-auditor.md');
        $this->assertFileExists($this->root.'/.claude/skills/laravel-testing/SKILL.md');

        $guidelines = $this->readFile('CLAUDE.md');
        $this->assertStringContainsString('### Subagents', $guidelines);
        $this->assertStringContainsString('`migration-reviewer` — Reviews Laravel migrations', $guidelines);
        $this->assertStringContainsString('`laravel-testing` — How to write', $guidelines);
    }

    public function test_every_shipped_resource_passes_validation(): void
    {
        $names = array_map(fn ($r): string => $r->type->value.'/'.$r->name, ClaudeSupport::resources());

        $this->assertSame([
            'commands/audit', 'commands/enterprise-redesign', 'commands/feature', 'commands/upgrade-laravel',
            'agents/migration-reviewer', 'agents/query-auditor',
            'skills/laravel-testing',
        ], $names);
    }

    public function test_project_skills_sync_with_all_their_files_and_can_be_toggled(): void
    {
        $this->source('skills/deploy/SKILL.md', "---\nname: deploy\ndescription: Deploy safely\n---\nSteps");
        $this->source('skills/deploy/scripts/check.sh', "#!/bin/sh\necho ok\n");

        $this->artisan('claude:install')->assertSuccessful();
        $this->assertSame("#!/bin/sh\necho ok\n", $this->readFile('.claude/skills/deploy/scripts/check.sh'));

        config()->set('claude-support.skills', ['deploy' => false]);
        $this->artisan('claude:install')->assertSuccessful();
        $this->assertDirectoryDoesNotExist($this->root.'/.claude/skills/deploy');
    }

    public function test_packages_can_register_a_whole_source(): void
    {
        $dir = $this->root.'/vendor-pkg';
        mkdir($dir.'/agents', 0777, true);
        file_put_contents($dir.'/agents/billing-expert.md', "---\nname: billing-expert\ndescription: Knows billing\n---\nYou know billing.");

        ClaudeSupport::source($dir);
        $this->artisan('claude:install')->assertSuccessful();

        $this->assertFileExists($this->root.'/.claude/agents/billing-expert.md');
    }

    public function test_resources_missing_required_front_matter_are_rejected(): void
    {
        $this->source('agents/helper.md', "---\ndescription: no name\n---\nBody");

        $this->artisan('claude:install')->expectsOutputToContain('needs a `name:` line')->assertFailed();
        $this->assertFileDoesNotExist($this->root.'/CLAUDE.md');
    }

    public function test_agent_names_must_match_their_file(): void
    {
        $this->source('agents/helper.md', "---\nname: other\ndescription: x\n---\nBody");

        $this->artisan('claude:install')->expectsOutputToContain('must match')->assertFailed();
    }

    public function test_enabling_an_unknown_resource_fails_loudly(): void
    {
        config()->set('claude-support.agents', ['ghost' => true]);

        $this->artisan('claude:install')->expectsOutputToContain('no source provides it')->assertFailed();
    }

    public function test_windows_line_endings_are_not_reported_as_local_edits(): void
    {
        $this->artisan('claude:install')->assertSuccessful();

        // What git with core.autocrlf=true produces on checkout.
        $path = '.claude/commands/audit.md';
        $this->writeFile($path, str_replace("\n", "\r\n", $this->readFile($path)));
        $this->writeFile('CLAUDE.md', str_replace("\n", "\r\n", $this->readFile('CLAUDE.md')));

        $this->assertTrue(ClaudeSupport::plan()->isEmpty());
    }

    public function test_team_language_is_written_into_the_guidelines(): void
    {
        config()->set('claude-support.guidelines.language', 'French');

        $this->artisan('claude:install')->assertSuccessful();

        $this->assertStringContainsString('Communicate with the team in French', $this->readFile('CLAUDE.md'));
    }

    public function test_status_can_report_json(): void
    {
        $this->artisan('claude:status --json --check')
            ->expectsOutputToContain('"in_sync": false')
            ->assertFailed();

        $this->artisan('claude:install')->assertSuccessful();
        $this->artisan('claude:status --json --check')
            ->expectsOutputToContain('"in_sync": true')
            ->assertSuccessful();
    }

    private function source(string $relative, string $contents): void
    {
        $this->writeFile('resources/claude/'.$relative, $contents);
        config()->set('claude-support.sources', [$this->root.'/resources/claude']);
    }
}
