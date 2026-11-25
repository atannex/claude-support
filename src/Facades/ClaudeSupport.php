<?php

declare(strict_types=1);

namespace Atannex\ClaudeSupport\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Atannex\ClaudeSupport\ClaudeSupport source(string $directory)
 * @method static \Atannex\ClaudeSupport\ClaudeSupport command(string $name, string $path)
 * @method static \Atannex\ClaudeSupport\ClaudeSupport agent(string $name, string $path)
 * @method static list<\Atannex\ClaudeSupport\Resources\ResourceDefinition> resources()
 * @method static string root()
 * @method static \Atannex\ClaudeSupport\Stack\Stack stack()
 * @method static \Atannex\ClaudeSupport\Sync\Plan plan(bool $force = false)
 * @method static \Atannex\ClaudeSupport\Sync\Plan uninstallPlan(bool $force = false)
 * @method static void apply(\Atannex\ClaudeSupport\Sync\Plan $plan)
 *
 * @see \Atannex\ClaudeSupport\ClaudeSupport
 */
final class ClaudeSupport extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Atannex\ClaudeSupport\ClaudeSupport::class;
    }
}
