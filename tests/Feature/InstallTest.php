<?php

declare(strict_types=1);

namespace Atannex\ClaudeSupport\Tests\Feature;

use Atannex\ClaudeSupport\Facades\ClaudeSupport;
use Atannex\ClaudeSupport\Guidelines\ManagedBlock;
use Atannex\ClaudeSupport\Sync\Action;
use Atannex\ClaudeSupport\Sync\Change;
use Atannex\ClaudeSupport\Tests\TestCase;

final class InstallTest extends TestCase
{
    private const COMMAND = '.claude/commands/enterprise-redesign.md';

    public function test_it_installs_the_command_guidelines_settings_and_manifest(): void
    {
        $this->writeFile('composer.json', json_encode(['require' => ['laravel/framework' => '^12.0'], 'require-dev' => ['laravel/pint' => '^1.0']]));

        $this->artisan('claude:install')->assertSuccessful();

        $this->assertSame(file_get_contents(__DIR__.'/../../resources/claude/commands/enterprise-redesign.md'), $this->readFile(self::COMMAND));

        $guidelines = $this->readFile('CLAUDE.md');
        $this->assertTrue(ManagedBlock::has($guidelines));
        $this->assertStringContainsString('Laravel ^12.0', $guidelines);
        $this->assertStringContainsString('`vendor/bin/pint --dirty`', $guidelines);
        $this->assertStringContainsString('`/enterprise-redesign` — Deeply audit and redesign', $guidelines);

        $this->assertSame(['Bash(php artisan test:*)', 'Bash(vendor/bin/pint:*)'], $this->readJson('.claude/settings.json')['permissions']['allow']);

        $manifest = $this->readJson('.claude/claude-support.json');
        $this->assertSame(hash('sha256', $this->readFile(self::COMMAND)), $manifest['files'][self::COMMAND]);
        $this->assertSame(['Bash(php artisan test:*)', 'Bash(vendor/bin/pint:*)'], $manifest['permissions']);
    }

    public function test_a_second_install_changes_nothing(): void
    {
        $this->artisan('claude:install')->assertSuccessful();

        $this->assertTrue(ClaudeSupport::plan()->isEmpty());
        $this->artisan('claude:status --check')->assertSuccessful();
    }

    public function test_status_check_fails_before_install(): void
    {
        $this->artisan('claude:status --check')->assertFailed();
        $this->artisan('claude:status')->assertSuccessful();
    }

    public function test_dry_run_writes_nothing(): void
    {
        $this->artisan('claude:install --dry-run')->assertSuccessful();

        $this->assertFileDoesNotExist($this->root.'/'.self::COMMAND);
        $this->assertFileDoesNotExist($this->root.'/CLAUDE.md');
    }

    public function test_unedited_files_follow_upstream_and_edited_ones_are_protected(): void
    {
        $source = $this->customCommand('review', "---\ndescription: v1\n---\nOne");
        $this->artisan('claude:install')->assertSuccessful();

        file_put_contents($source, "---\ndescription: v2\n---\nTwo");
        $this->artisan('claude:install')->assertSuccessful();
        $this->assertStringContainsString('Two', $this->readFile('.claude/commands/review.md'));

        $this->writeFile('.claude/commands/review.md', 'my local tweak');
        file_put_contents($source, "---\ndescription: v3\n---\nThree");

        $change = $this->change('.claude/commands/review.md');
        $this->assertSame(Action::Skip, $change->action);
        $this->artisan('claude:install')->assertSuccessful();
        $this->assertSame('my local tweak', $this->readFile('.claude/commands/review.md'));

        $this->artisan('claude:install --force')->assertSuccessful();
        $this->assertStringContainsString('Three', $this->readFile('.claude/commands/review.md'));
        $this->assertTrue(ClaudeSupport::plan()->isEmpty());
    }

    public function test_existing_unmanaged_files_are_never_replaced_without_force(): void
    {
        $this->writeFile(self::COMMAND, 'hand written');
        $this->assertSame(Action::Skip, $this->change(self::COMMAND)->action);

        $this->artisan('claude:install')->assertSuccessful();
        $this->assertSame('hand written', $this->readFile(self::COMMAND));
    }

    public function test_identical_unmanaged_files_are_adopted(): void
    {
        $this->writeFile(self::COMMAND, (string) file_get_contents(__DIR__.'/../../resources/claude/commands/enterprise-redesign.md'));

        $this->assertSame(Action::Adopt, $this->change(self::COMMAND)->action);
        $this->artisan('claude:install')->assertSuccessful();
        $this->assertArrayHasKey(self::COMMAND, $this->readJson('.claude/claude-support.json')['files']);
    }

    public function test_disabling_a_command_removes_it_unless_edited(): void
    {
        $this->customCommand('one', "---\ndescription: One\n---\n");
        $this->customCommand('two', "---\ndescription: Two\n---\n");
        $this->artisan('claude:install')->assertSuccessful();
        $this->writeFile('.claude/commands/two.md', 'edited');

        config()->set('claude-support.commands', ['one' => false, 'two' => false]);
        $this->artisan('claude:install')->assertSuccessful();

        $this->assertFileDoesNotExist($this->root.'/.claude/commands/one.md');
        $this->assertSame('edited', $this->readFile('.claude/commands/two.md'));
        $this->assertArrayNotHasKey('.claude/commands/two.md', $this->readJson('.claude/claude-support.json')['files']);
    }

    public function test_project_commands_override_shipped_ones(): void
    {
        $this->customCommand('enterprise-redesign', "---\ndescription: Ours\n---\nOur own brief");

        $this->artisan('claude:install')->assertSuccessful();

        $this->assertStringContainsString('Our own brief', $this->readFile(self::COMMAND));
    }

    public function test_packages_can_register_commands(): void
    {
        $path = $this->root.'/vendor-cmd.md';
        file_put_contents($path, "---\ndescription: From a package\n---\nBody");

        ClaudeSupport::command('package-audit', $path);
        $this->artisan('claude:install')->assertSuccessful();

        $this->assertSame((string) file_get_contents($path), $this->readFile('.claude/commands/package-audit.md'));
        $this->assertStringContainsString('`/package-audit` — From a package', $this->readFile('CLAUDE.md'));
    }

    public function test_unsafe_command_names_and_paths_are_rejected(): void
    {
        $this->customCommand('Bad_Name', "---\ndescription: x\n---\n");
        $this->artisan('claude:install')->assertFailed();

        config()->set('claude-support.sources', []);
        config()->set('claude-support.guidelines.file', '../outside.md');
        $this->artisan('claude:install')->assertFailed();
        $this->assertFileDoesNotExist(dirname($this->root).'/outside.md');
    }

    public function test_guidelines_keep_project_content_and_can_be_disabled(): void
    {
        $this->writeFile('CLAUDE.md', "# Team rules\n\nAlways be kind.\n");

        $this->artisan('claude:install')->assertSuccessful();
        $this->assertStringStartsWith("# Team rules\n\nAlways be kind.\n\n".ManagedBlock::START, $this->readFile('CLAUDE.md'));

        config()->set('claude-support.guidelines.enabled', false);
        $this->artisan('claude:install')->assertSuccessful();
        $this->assertSame("# Team rules\n\nAlways be kind.\n", $this->readFile('CLAUDE.md'));
    }

    private function customCommand(string $name, string $contents): string
    {
        $dir = $this->root.'/resources/claude/commands';
        @mkdir($dir, 0777, true);
        file_put_contents("{$dir}/{$name}.md", $contents);
        config()->set('claude-support.sources', [dirname($dir)]);

        return "{$dir}/{$name}.md";
    }

    private function change(string $path): Change
    {
        foreach (ClaudeSupport::plan()->changes as $change) {
            if ($change->path === $path) {
                return $change;
            }
        }

        $this->fail("No change planned for {$path}");
    }
}
