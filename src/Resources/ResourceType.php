<?php

declare(strict_types=1);

namespace Atannex\ClaudeSupport\Resources;

/**
 * The three ways Claude Code can be extended from a repository.
 *
 * A source directory mirrors .claude/: commands/*.md, agents/*.md and
 * skills/{name}/SKILL.md (plus any supporting files beside it).
 */
enum ResourceType: string
{
    case Command = 'commands';
    case Agent = 'agents';
    case Skill = 'skills';

    public function label(): string
    {
        return match ($this) {
            self::Command => 'command',
            self::Agent => 'agent',
            self::Skill => 'skill',
        };
    }

    /** @return list<string> front matter keys Claude Code needs to load the resource at all */
    public function requiredFrontMatter(): array
    {
        return match ($this) {
            self::Command => ['description'],
            self::Agent, self::Skill => ['name', 'description'],
        };
    }

    /** The file whose front matter describes the resource, relative to the resource root. */
    public function entryFile(string $name): string
    {
        return $this === self::Skill ? 'SKILL.md' : "{$name}.md";
    }

    /** Where a resource's file lands in the project, relative to the project root. */
    public function target(string $name, string $relative): string
    {
        return $this === self::Skill
            ? ".claude/skills/{$name}/{$relative}"
            : ".claude/{$this->value}/{$name}.md";
    }
}
