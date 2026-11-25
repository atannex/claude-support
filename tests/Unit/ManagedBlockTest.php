<?php

declare(strict_types=1);

namespace Atannex\ClaudeSupport\Tests\Unit;

use Atannex\ClaudeSupport\Guidelines\ManagedBlock;
use PHPUnit\Framework\TestCase;

final class ManagedBlockTest extends TestCase
{
    public function test_it_appends_to_an_existing_document_without_touching_it(): void
    {
        $doc = "# My app\n\nTeam notes.\n";

        $result = ManagedBlock::splice($doc, 'generated');

        $this->assertStringStartsWith("# My app\n\nTeam notes.\n\n".ManagedBlock::START, $result);
        $this->assertStringContainsString("generated\n".ManagedBlock::END, $result);
    }

    public function test_it_replaces_only_the_block(): void
    {
        $doc = ManagedBlock::splice("Before.\n", 'old');
        $doc .= "\nAfter.\n";

        $result = ManagedBlock::splice($doc, 'new');

        $this->assertStringNotContainsString('old', $result);
        $this->assertStringContainsString('new', $result);
        $this->assertStringStartsWith('Before.', $result);
        $this->assertStringEndsWith("After.\n", $result);
    }

    public function test_splice_is_idempotent(): void
    {
        $once = ManagedBlock::splice("Notes\n", 'body');

        $this->assertSame($once, ManagedBlock::splice($once, 'body'));
    }

    public function test_strip_restores_the_surrounding_document(): void
    {
        $doc = ManagedBlock::splice("Before.\n", 'body')."\nAfter.\n";

        $this->assertSame("Before.\n\nAfter.\n", ManagedBlock::strip($doc));
        $this->assertSame('', ManagedBlock::strip(ManagedBlock::splice('', 'body')));
    }

    public function test_a_start_marker_without_an_end_marker_never_eats_content(): void
    {
        $doc = "Intro.\n".ManagedBlock::START."\nold body, end marker deleted by hand\n\n## Team rules\n\nKeep me.\n";

        $this->assertTrue(ManagedBlock::broken($doc));

        // Even if a block is appended later, the next splice must not swallow the text in between.
        $twice = ManagedBlock::splice($doc.ManagedBlock::START."\nx\n".ManagedBlock::END."\n", 'new');
        $this->assertStringContainsString('Keep me.', $twice);
        $this->assertStringContainsString("new\n".ManagedBlock::END, $twice);
    }
}
