<?php

declare(strict_types=1);

namespace Atannex\ClaudeSupport\Resources;

final readonly class ResourceDefinition
{
    /**
     * @param  array<string, string>  $files  path relative to the resource root => absolute source path
     */
    public function __construct(
        public ResourceType $type,
        public string $name,
        public string $description,
        public array $files,
        public string $origin,
    ) {}

    /** How Claude Code users invoke or recognise it, for the CLAUDE.md listing. */
    public function handle(): string
    {
        return $this->type === ResourceType::Command ? "/{$this->name}" : $this->name;
    }
}
