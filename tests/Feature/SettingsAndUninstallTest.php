<?php

declare(strict_types=1);

namespace Atannex\ClaudeSupport\Tests\Feature;

use Atannex\ClaudeSupport\Tests\TestCase;

final class SettingsAndUninstallTest extends TestCase
{
    public function test_settings_are_merged_without_losing_existing_content(): void
    {
        $this->writeFile('.claude/settings.json', json_encode([
            'env' => new \stdClass,
            'permissions' => ['allow' => ['Bash(npm run build:*)', 'Bash(php artisan test:*)'], 'deny' => ['Read(.env)']],
        ]));
        config()->set('claude-support.settings.permissions', ['Bash(npm run lint:*)']);

        $this->artisan('claude:install')->assertSuccessful();

        $raw = $this->readFile('.claude/settings.json');
        $this->assertStringContainsString('"env": {}', $raw, 'empty objects must stay objects');
        $settings = $this->readJson('.claude/settings.json');
        $this->assertSame(['Bash(npm run build:*)', 'Bash(php artisan test:*)', 'Bash(npm run lint:*)'], $settings['permissions']['allow']);
        $this->assertSame(['Read(.env)'], $settings['permissions']['deny']);

        // The artisan test entry pre-existed, so it is the project's, not ours.
        $this->assertSame(['Bash(npm run lint:*)'], $this->readJson('.claude/claude-support.json')['permissions']);
    }

    public function test_permissions_we_stop_wanting_are_removed_but_project_ones_stay(): void
    {
        config()->set('claude-support.settings.permissions', ['Bash(npm run lint:*)']);
        $this->artisan('claude:install')->assertSuccessful();
        $this->assertContains('Bash(npm run lint:*)', $this->readJson('.claude/settings.json')['permissions']['allow']);

        config()->set('claude-support.settings.permissions', []);
        $this->artisan('claude:install')->assertSuccessful();
        $this->assertSame(['Bash(php artisan test:*)'], $this->readJson('.claude/settings.json')['permissions']['allow']);
    }

    public function test_invalid_settings_are_reported_and_left_untouched(): void
    {
        $this->writeFile('.claude/settings.json', '{ broken');

        $this->artisan('claude:install')->assertSuccessful();

        $this->assertSame('{ broken', $this->readFile('.claude/settings.json'));
        $this->assertFileExists($this->root.'/.claude/commands/enterprise-redesign.md');
    }

    public function test_uninstall_removes_only_what_the_package_added(): void
    {
        $this->writeFile('CLAUDE.md', "# Team rules\n");
        $this->writeFile('.claude/settings.json', json_encode(['permissions' => ['allow' => ['Bash(git status)']]]));
        $this->writeFile('.claude/agents/reviewer.md', 'ours, not the package\'s');

        $this->artisan('claude:install')->assertSuccessful();
        $this->artisan('claude:uninstall')->expectsConfirmation('Apply the changes above?', 'yes')->assertSuccessful();

        $this->assertSame("# Team rules\n", $this->readFile('CLAUDE.md'));
        $this->assertSame(['Bash(git status)'], $this->readJson('.claude/settings.json')['permissions']['allow']);
        $this->assertFileExists($this->root.'/.claude/agents/reviewer.md');
        $this->assertDirectoryDoesNotExist($this->root.'/.claude/commands');
        $this->assertFileDoesNotExist($this->root.'/.claude/claude-support.json');
    }

    public function test_uninstall_on_a_fresh_project_leaves_no_trace(): void
    {
        $this->artisan('claude:install')->assertSuccessful();
        $this->artisan('claude:uninstall')->expectsConfirmation('Apply the changes above?', 'yes')->assertSuccessful();

        $this->assertFileDoesNotExist($this->root.'/CLAUDE.md');
        $this->assertDirectoryDoesNotExist($this->root.'/.claude');
    }

    public function test_uninstall_keeps_edited_files_unless_forced(): void
    {
        $this->artisan('claude:install')->assertSuccessful();
        $this->writeFile('.claude/commands/enterprise-redesign.md', 'tuned for our team');

        $this->artisan('claude:uninstall')->expectsConfirmation('Apply the changes above?', 'yes')->assertSuccessful();
        $this->assertSame('tuned for our team', $this->readFile('.claude/commands/enterprise-redesign.md'));

        $this->artisan('claude:install --force')->assertSuccessful();
        $this->writeFile('.claude/commands/enterprise-redesign.md', 'tuned again');
        $this->artisan('claude:uninstall --force')->expectsConfirmation('Apply the changes above?', 'yes')->assertSuccessful();
        $this->assertFileDoesNotExist($this->root.'/.claude/commands/enterprise-redesign.md');
    }
}
