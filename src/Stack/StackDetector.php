<?php

declare(strict_types=1);

namespace Atannex\ClaudeSupport\Stack;

use Illuminate\Filesystem\Filesystem;

/**
 * Reads composer.json, composer.lock and package.json at the project root.
 * Missing or unreadable manifests simply yield fewer facts.
 */
final class StackDetector
{
    /** Composer packages worth telling Claude about, => display name. */
    private const NOTABLE = [
        'laravel/sanctum' => 'Sanctum',
        'laravel/passport' => 'Passport',
        'laravel/fortify' => 'Fortify',
        'laravel/horizon' => 'Horizon',
        'laravel/octane' => 'Octane',
        'laravel/reverb' => 'Reverb',
        'laravel/scout' => 'Scout',
        'laravel/cashier' => 'Cashier',
        'laravel/pennant' => 'Pennant',
        'laravel/pulse' => 'Pulse',
        'laravel/telescope' => 'Telescope',
        'livewire/livewire' => 'Livewire',
        'livewire/volt' => 'Volt',
        'inertiajs/inertia-laravel' => 'Inertia',
        'filament/filament' => 'Filament',
        'spatie/laravel-permission' => 'spatie/laravel-permission',
        'spatie/laravel-data' => 'spatie/laravel-data',
        'spatie/laravel-activitylog' => 'spatie/laravel-activitylog',
    ];

    private const FRONTEND = [
        'react' => 'React',
        'vue' => 'Vue',
        'astro' => 'Astro',
        'solid-js' => 'Solid.js',
        'preact' => 'Preact',
        'angular' => 'Angular',
        'ember' => 'Ember',
        'lit' => 'Lit',
        'polymer' => 'Polymer',
        'jquery' => 'jQuery',
        'backbone' => 'Backbone.js',
        'knockout' => 'Knockout.js',
        'mithril' => 'Mithril.js',
        'riot' => 'Riot.js',
        'stimulus' => 'Stimulus',
        'jquery-ui' => 'jQuery UI',
        'bootstrap' => 'Bootstrap',
        'foundation-sites' => 'Foundation',
        'bulma' => 'Bulma',
        'materialize-css' => 'Materialize',
        'semantic-ui' => 'Semantic UI',
        'tailwindcss' => 'Tailwind CSS',
        'material-ui' => 'Material-UI',
        'ant-design' => 'Ant Design',
        'chakra-ui' => 'Chakra UI',
        'vuetify' => 'Vuetify',
        'quasar' => 'Quasar',
        'sveltekit' => 'SvelteKit',
        'solid-start' => 'Solid Start',
        'next' => 'Next.js',
        'nuxt' => 'Nuxt.js',
        'svelte' => 'Svelte',
        'alpinejs' => 'Alpine.js',
        'typescript' => 'TypeScript',
        'vite' => 'Vite',
        'webpack' => 'Webpack',
        'rollup' => 'Rollup',
        'parcel' => 'Parcel',
        'esbuild' => 'esbuild',
        'snowpack' => 'Snowpack',
        'gulp' => 'Gulp',
        'grunt' => 'Grunt',
        'browserify' => 'Browserify',
        'react-router' => 'React Router',
        'react-redux' => 'React Redux',
        'react-query' => 'React Query',
        'react-hook-form' => 'React Hook Form',
        'react-i18next' => 'React i18next',
        'react-dnd' => 'React DnD',
        'react-spring' => 'React Spring',
        'react-three-fiber' => 'React Three Fiber',
        'react-native' => 'React Native',
        'vue-router' => 'Vue Router',
        'vuex' => 'Vuex',
        'pinia' => 'Pinia',
        'vue-i18n' => 'Vue I18n',
        'vue-apollo' => 'Vue Apollo',
        'vue-chartjs' => 'Vue Chart.js',
        'vue-formulate' => 'Vue Formulate',
        'vue-tailwind' => 'Vue Tailwind',
        'vue-toastification' => 'Vue Toastification',
        'vueuse' => 'VueUse',
        'vue3-google-map' => 'Vue 3 Google Map',
        'vue3-datepicker' => 'Vue 3 Datepicker',
        'vue3-select' => 'Vue 3 Select',
        'vue3-carousel' => 'Vue 3 Carousel',
        'vue3-virtual-scroller' => 'Vue 3 Virtual Scroller',
        'vue3-clipboard' => 'Vue 3 Clipboard',
        'vue3-qr-code' => 'Vue 3 QR Code',
        'vue3-pdf' => 'Vue 3 PDF',
    ];

    public function __construct(private readonly Filesystem $files) {}

    public function detect(string $root, ?string $database = null): Stack
    {
        $composer = $this->json($root.'/composer.json');
        $installed = $this->installedVersions($root);
        $declared = [...($composer['require'] ?? []), ...($composer['require-dev'] ?? [])];
        $has = fn (string $package): bool => isset($installed[$package]) || isset($declared[$package]);
        $version = fn (string $package): ?string => $installed[$package] ?? $declared[$package] ?? null;

        $packages = [];
        foreach (self::NOTABLE as $package => $label) {
            if ($has($package)) {
                $packages[$label] = (string) $version($package);
            }
        }

        // atannex/* packages are first-party here: always worth naming.
        foreach (array_keys([...$declared, ...$installed]) as $package) {
            if (str_starts_with((string) $package, 'atannex/') && $package !== 'atannex/claude-support') {
                $packages[$package] = (string) $version($package);
            }
        }

        $node = $this->json($root.'/package.json');
        $nodeDeps = [...($node['dependencies'] ?? []), ...($node['devDependencies'] ?? [])];
        $frontend = [];
        foreach (self::FRONTEND as $dep => $label) {
            if (isset($nodeDeps[$dep])) {
                $frontend[] = $label;
            }
        }

        return new Stack(
            php: isset($declared['php']) ? (string) $declared['php'] : null,
            laravel: $version('laravel/framework'),
            testRunner: $has('pestphp/pest') ? 'Pest' : 'PHPUnit',
            pint: $has('laravel/pint'),
            phpstan: $has('larastan/larastan') || $has('phpstan/phpstan') || $has('nunomaduro/larastan'),
            packages: $packages,
            frontend: $frontend,
            composerScripts: array_map('strval', array_keys($composer['scripts'] ?? [])),
            database: $database,
        );
    }

    /** @return array<string, string> package => version, from composer.lock */
    private function installedVersions(string $root): array
    {
        $lock = $this->json($root.'/composer.lock');
        $versions = [];

        foreach ([...($lock['packages'] ?? []), ...($lock['packages-dev'] ?? [])] as $package) {
            if (isset($package['name'], $package['version'])) {
                $versions[$package['name']] = ltrim((string) $package['version'], 'v');
            }
        }

        return $versions;
    }

    /** @return array<string, mixed> */
    private function json(string $path): array
    {
        if (! $this->files->isFile($path)) {
            return [];
        }

        $data = json_decode((string) $this->files->get($path), true);

        return is_array($data) ? $data : [];
    }
}
