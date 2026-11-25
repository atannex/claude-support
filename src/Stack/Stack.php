<?php

declare(strict_types=1);

namespace Atannex\ClaudeSupport\Stack;

/**
 * What the host project is built with, as far as its manifests say.
 * Every field is a detected fact; nothing here is guessed.
 */
final readonly class Stack
{
    /**
     * @param  array<string, string>  $packages  notable composer packages => installed version
     * @param  list<string>  $frontend  detected frontend tooling, display names
     * @param  list<string>  $composerScripts
     */
    public function __construct(
        public ?string $php = null,
        public ?string $laravel = null,
        public string $testRunner = 'PHPUnit',
        public bool $pint = false,
        public bool $phpstan = false,
        public array $packages = [],
        public array $frontend = [],
        public array $composerScripts = [],
        public ?string $database = null,
    ) {}

    public function hasComposerScript(string $name): bool
    {
        return in_array($name, $this->composerScripts, true);
    }
}
