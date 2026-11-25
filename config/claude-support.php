<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Project root
    |--------------------------------------------------------------------------
    |
    | Where the .claude/ directory and CLAUDE.md live. Null means base_path().
    |
    */

    'path' => env('CLAUDE_SUPPORT_PATH'),

    /*
    |--------------------------------------------------------------------------
    | Commands, agents and skills
    |--------------------------------------------------------------------------
    |
    | Everything this package ships is installed by default:
    |
    |   commands  .claude/commands/{name}.md     invoked as /{name}
    |   agents    .claude/agents/{name}.md       subagents Claude delegates to
    |   skills    .claude/skills/{name}/SKILL.md loaded automatically when relevant
    |
    | Set a name to false to leave it out, or true to require it (the install
    | fails if no source provides it). Toggles also apply to resources from
    | other packages and from your own sources.
    |
    */

    'commands' => [
        // 'enterprise-redesign' => true,
        // 'upgrade-laravel' => false,
    ],

    'agents' => [],

    'skills' => [],

    /*
    |--------------------------------------------------------------------------
    | Your own sources
    |--------------------------------------------------------------------------
    |
    | Directories laid out like .claude/ (commands/, agents/, skills/) holding
    | your organisation's resources. They are synced like the shipped ones and
    | replace a shipped or package resource with the same type and name.
    |
    */

    'sources' => [
        resource_path('claude'),
    ],

    /*
    |--------------------------------------------------------------------------
    | CLAUDE.md guidelines
    |--------------------------------------------------------------------------
    |
    | A managed block in CLAUDE.md describing the detected stack, how to
    | verify work, the team's language and the conventions below. Content outside the block is
    | never touched; content inside it is regenerated on every install.
    |
    */

    'guidelines' => [
        'enabled' => true,
        'file' => 'CLAUDE.md',

        // The language your team works in, e.g. 'French'. Claude then talks to
        // you in it while code, comments and commits stay in English.
        'language' => env('CLAUDE_SUPPORT_LANGUAGE'),

        'conventions' => [
            'Validate input in Form Requests and authorize with policies or gates; never trust request data inside controllers.',
            'Keep business logic in actions or services, not controllers, models\' accessors or Blade views.',
            'Wrap multi-step writes in DB::transaction() and dispatch side effects after commit.',
            'Eager-load relationships to avoid N+1 queries; paginate or chunk anything unbounded.',
            'Read env() only inside config files; use config() everywhere else.',
            'Never edit a migration that has run in production; add a new one.',
            'Move slow or external work (mail, HTTP, exports) to queued jobs.',
            'Add or update tests for every behaviour change and run them before finishing.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Claude Code settings
    |--------------------------------------------------------------------------
    |
    | Permissions merged into .claude/settings.json so routine verification
    | commands don't prompt. Detected tools (Pint, PHPStan, composer test)
    | are added automatically; list extras here. Only entries this package
    | added are ever removed again.
    |
    */

    'settings' => [
        'enabled' => true,
        'file' => '.claude/settings.json',
        'permissions' => [],
    ],

];
