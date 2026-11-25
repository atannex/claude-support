<?php

declare(strict_types=1);

namespace Atannex\ClaudeSupport\Stack;

/**
 * The packages worth telling Claude about, grouped the way they appear in CLAUDE.md.
 *
 * Every key is the exact name published on Packagist or npm. A key ending in
 * "/" matches a whole scope (e.g. every "@radix-ui/*" package). Entries are
 * architectural: they change how code in the project should be written.
 * Individual widgets (date pickers, carousels…) are deliberately left out.
 */
final class Catalog
{
    /** @var array<string, array<string, string>> category => [composer package => label] */
    public const COMPOSER = [
        'Auth & access' => [
            'laravel/sanctum' => 'Sanctum',
            'laravel/passport' => 'Passport',
            'laravel/fortify' => 'Fortify',
            'laravel/socialite' => 'Socialite',
            'laravel/jetstream' => 'Jetstream',
            'laravel/breeze' => 'Breeze',
            'spatie/laravel-permission' => 'spatie/laravel-permission',
            'bezhansalleh/filament-shield' => 'bezhansalleh/filament-shield',
            'tymon/jwt-auth' => 'tymon/jwt-auth',
            'php-open-source-saver/jwt-auth' => 'php-open-source-saver/jwt-auth',
            'pragmarx/google2fa-laravel' => 'pragmarx/google2fa-laravel',
        ],
        'UI & admin' => [
            'livewire/livewire' => 'Livewire',
            'livewire/volt' => 'Volt',
            'livewire/flux' => 'Flux',
            'inertiajs/inertia-laravel' => 'Inertia',
            'filament/filament' => 'Filament',
            'laravel/nova' => 'Nova',
            'laravel/folio' => 'Folio',
            'laravel/wayfinder' => 'Wayfinder',
            'tightenco/ziggy' => 'Ziggy',
            'blade-ui-kit/blade-icons' => 'blade-ui-kit/blade-icons',
        ],
        'Infrastructure' => [
            'laravel/horizon' => 'Horizon',
            'laravel/octane' => 'Octane',
            'laravel/reverb' => 'Reverb',
            'laravel/pennant' => 'Pennant',
            'laravel/vapor-core' => 'Vapor',
            'laravel/sail' => 'Sail',
            'predis/predis' => 'predis/predis',
            'pusher/pusher-php-server' => 'pusher/pusher-php-server',
            'league/flysystem-aws-s3-v3' => 'league/flysystem-aws-s3-v3',
            'aws/aws-sdk-php' => 'aws/aws-sdk-php',
        ],
        'Search' => [
            'laravel/scout' => 'Scout',
            'meilisearch/meilisearch-php' => 'meilisearch/meilisearch-php',
            'typesense/typesense-php' => 'typesense/typesense-php',
            'algolia/algoliasearch-client-php' => 'algolia/algoliasearch-client-php',
            'elasticsearch/elasticsearch' => 'elasticsearch/elasticsearch',
        ],
        'Domain & data' => [
            'spatie/laravel-data' => 'spatie/laravel-data',
            'lorisleiva/laravel-actions' => 'lorisleiva/laravel-actions',
            'spatie/laravel-model-states' => 'spatie/laravel-model-states',
            'spatie/laravel-event-sourcing' => 'spatie/laravel-event-sourcing',
            'spatie/laravel-query-builder' => 'spatie/laravel-query-builder',
            'spatie/laravel-medialibrary' => 'spatie/laravel-medialibrary',
            'spatie/laravel-settings' => 'spatie/laravel-settings',
            'spatie/laravel-tags' => 'spatie/laravel-tags',
            'spatie/laravel-sluggable' => 'spatie/laravel-sluggable',
            'spatie/laravel-translatable' => 'spatie/laravel-translatable',
            'astrotomic/laravel-translatable' => 'astrotomic/laravel-translatable',
            'mcamara/laravel-localization' => 'mcamara/laravel-localization',
            'maatwebsite/excel' => 'maatwebsite/excel',
            'barryvdh/laravel-dompdf' => 'barryvdh/laravel-dompdf',
            'spatie/laravel-pdf' => 'spatie/laravel-pdf',
            'spatie/browsershot' => 'spatie/browsershot',
            'intervention/image' => 'intervention/image',
            'spatie/laravel-sitemap' => 'spatie/laravel-sitemap',
        ],
        'Auditing & backups' => [
            'spatie/laravel-activitylog' => 'spatie/laravel-activitylog',
            'owen-it/laravel-auditing' => 'owen-it/laravel-auditing',
            'spatie/laravel-backup' => 'spatie/laravel-backup',
        ],
        'Multi-tenancy' => [
            'stancl/tenancy' => 'stancl/tenancy',
            'spatie/laravel-multitenancy' => 'spatie/laravel-multitenancy',
        ],
        'Payments' => [
            'laravel/cashier' => 'Cashier (Stripe)',
            'laravel/cashier-paddle' => 'Cashier (Paddle)',
            'stripe/stripe-php' => 'stripe/stripe-php',
            'mollie/laravel-mollie' => 'mollie/laravel-mollie',
            'srmklive/paypal' => 'srmklive/paypal',
        ],
        'APIs & AI' => [
            'dedoc/scramble' => 'dedoc/scramble',
            'knuckleswtf/scribe' => 'knuckleswtf/scribe',
            'darkaonline/l5-swagger' => 'darkaonline/l5-swagger',
            'laravel/mcp' => 'Laravel MCP',
            'laravel/boost' => 'Boost',
            'prism-php/prism' => 'prism-php/prism',
            'openai-php/laravel' => 'openai-php/laravel',
        ],
        'Observability' => [
            'laravel/telescope' => 'Telescope',
            'laravel/pulse' => 'Pulse',
            'laravel/nightwatch' => 'Nightwatch',
            'laravel/pail' => 'Pail',
            'sentry/sentry-laravel' => 'sentry/sentry-laravel',
            'bugsnag/bugsnag-laravel' => 'bugsnag/bugsnag-laravel',
            'spatie/laravel-health' => 'spatie/laravel-health',
            'barryvdh/laravel-debugbar' => 'barryvdh/laravel-debugbar',
            'spatie/laravel-ray' => 'spatie/laravel-ray',
            'itsgoingd/clockwork' => 'itsgoingd/clockwork',
        ],
        'Quality & testing' => [
            'laravel/dusk' => 'Dusk',
            'mockery/mockery' => 'Mockery',
            'brianium/paratest' => 'ParaTest',
            'rector/rector' => 'Rector',
            'driftingly/rector-laravel' => 'driftingly/rector-laravel',
            'vimeo/psalm' => 'Psalm',
            'infection/infection' => 'Infection',
            'friendsofphp/php-cs-fixer' => 'PHP CS Fixer',
            'squizlabs/php_codesniffer' => 'PHP_CodeSniffer',
        ],
    ];

    /** @var array<string, array<string, string>> category => [npm package or "@scope/" => label] */
    public const NPM = [
        'Frameworks' => [
            'react' => 'React',
            'vue' => 'Vue',
            'svelte' => 'Svelte',
            'solid-js' => 'Solid',
            'preact' => 'Preact',
            '@angular/core' => 'Angular',
            'ember-source' => 'Ember',
            'lit' => 'Lit',
            '@polymer/polymer' => 'Polymer',
            '@builder.io/qwik' => 'Qwik',
            'alpinejs' => 'Alpine.js',
            '@hotwired/stimulus' => 'Stimulus',
            '@hotwired/turbo' => 'Turbo',
            'htmx.org' => 'htmx',
            'jquery' => 'jQuery',
            'backbone' => 'Backbone.js',
            'knockout' => 'Knockout',
            'mithril' => 'Mithril',
            'riot' => 'Riot',
            'react-native' => 'React Native',
        ],
        'Meta-frameworks' => [
            'next' => 'Next.js',
            'nuxt' => 'Nuxt',
            '@sveltejs/kit' => 'SvelteKit',
            '@solidjs/start' => 'SolidStart',
            '@remix-run/react' => 'Remix',
            'astro' => 'Astro',
        ],
        'Laravel bridges' => [
            '@inertiajs/react' => 'Inertia (React)',
            '@inertiajs/vue3' => 'Inertia (Vue)',
            '@inertiajs/svelte' => 'Inertia (Svelte)',
            'laravel-echo' => 'Laravel Echo',
            '@laravel/echo-react' => 'Laravel Echo',
            '@laravel/echo-vue' => 'Laravel Echo',
            'laravel-precognition-react' => 'Precognition',
            'laravel-precognition-vue' => 'Precognition',
            'laravel-precognition-alpine' => 'Precognition',
            'ziggy-js' => 'Ziggy',
            'laravel-vue-i18n' => 'laravel-vue-i18n',
        ],
        'Styling & components' => [
            'tailwindcss' => 'Tailwind CSS',
            'bootstrap' => 'Bootstrap',
            'foundation-sites' => 'Foundation',
            'bulma' => 'Bulma',
            'materialize-css' => 'Materialize',
            'semantic-ui-css' => 'Semantic UI',
            'daisyui' => 'daisyUI',
            'flowbite' => 'Flowbite',
            '@mui/material' => 'Material UI',
            'antd' => 'Ant Design',
            '@chakra-ui/react' => 'Chakra UI',
            '@mantine/core' => 'Mantine',
            '@radix-ui/' => 'Radix UI',
            '@headlessui/react' => 'Headless UI',
            '@headlessui/vue' => 'Headless UI',
            'reka-ui' => 'Reka UI',
            'radix-vue' => 'Radix Vue',
            'bits-ui' => 'Bits UI',
            'vuetify' => 'Vuetify',
            'quasar' => 'Quasar',
            'primevue' => 'PrimeVue',
            'primereact' => 'PrimeReact',
            'element-plus' => 'Element Plus',
            'naive-ui' => 'Naive UI',
            '@nuxt/ui' => 'Nuxt UI',
            'jquery-ui' => 'jQuery UI',
            'styled-components' => 'styled-components',
            '@emotion/react' => 'Emotion',
            'sass' => 'Sass',
            'less' => 'Less',
        ],
        'State & data' => [
            '@reduxjs/toolkit' => 'Redux Toolkit',
            'redux' => 'Redux',
            'react-redux' => 'React Redux',
            'zustand' => 'Zustand',
            'jotai' => 'Jotai',
            'mobx' => 'MobX',
            'xstate' => 'XState',
            'pinia' => 'Pinia',
            'vuex' => 'Vuex',
            '@tanstack/react-query' => 'TanStack Query',
            '@tanstack/vue-query' => 'TanStack Query',
            'react-query' => 'React Query (v3)',
            'swr' => 'SWR',
            '@apollo/client' => 'Apollo Client',
            '@vue/apollo-composable' => 'Vue Apollo',
            'vue-apollo' => 'Vue Apollo',
            'axios' => 'Axios',
            '@vueuse/core' => 'VueUse',
        ],
        'Routing' => [
            'react-router' => 'React Router',
            'react-router-dom' => 'React Router',
            '@tanstack/react-router' => 'TanStack Router',
            'vue-router' => 'Vue Router',
        ],
        'Forms & validation' => [
            'react-hook-form' => 'React Hook Form',
            'formik' => 'Formik',
            '@formkit/vue' => 'FormKit',
            'vee-validate' => 'VeeValidate',
            '@vuelidate/core' => 'Vuelidate',
            'zod' => 'Zod',
            'yup' => 'Yup',
            'valibot' => 'Valibot',
        ],
        'Internationalisation' => [
            'i18next' => 'i18next',
            'react-i18next' => 'react-i18next',
            'vue-i18n' => 'Vue I18n',
        ],
        'Realtime' => [
            'pusher-js' => 'Pusher',
            'socket.io-client' => 'Socket.IO',
        ],
        'Charts, graphics & motion' => [
            'chart.js' => 'Chart.js',
            'vue-chartjs' => 'vue-chartjs',
            'react-chartjs-2' => 'react-chartjs-2',
            'apexcharts' => 'ApexCharts',
            'echarts' => 'Apache ECharts',
            'highcharts' => 'Highcharts',
            'recharts' => 'Recharts',
            'd3' => 'D3',
            'three' => 'Three.js',
            '@react-three/fiber' => 'React Three Fiber',
            'framer-motion' => 'Framer Motion',
            'motion' => 'Motion',
            'gsap' => 'GSAP',
            '@react-spring/web' => 'React Spring',
            'react-spring' => 'React Spring',
            'react-dnd' => 'React DnD',
            '@dnd-kit/core' => 'dnd kit',
        ],
        'Build' => [
            'vite' => 'Vite',
            'laravel-vite-plugin' => 'laravel-vite-plugin',
            'laravel-mix' => 'Laravel Mix',
            'webpack' => 'webpack',
            '@rspack/core' => 'Rspack',
            'rollup' => 'Rollup',
            'parcel' => 'Parcel',
            'esbuild' => 'esbuild',
            'turbo' => 'Turborepo',
            'gulp' => 'Gulp',
            'grunt' => 'Grunt',
            'browserify' => 'Browserify',
            'snowpack' => 'Snowpack',
        ],
        'Testing' => [
            'vitest' => 'Vitest',
            'jest' => 'Jest',
            'mocha' => 'Mocha',
            '@playwright/test' => 'Playwright',
            'cypress' => 'Cypress',
            'puppeteer' => 'Puppeteer',
            '@testing-library/react' => 'Testing Library',
            '@testing-library/vue' => 'Testing Library',
            '@vue/test-utils' => 'Vue Test Utils',
            'storybook' => 'Storybook',
        ],
        'Language & linting' => [
            'typescript' => 'TypeScript',
            'eslint' => 'ESLint',
            'prettier' => 'Prettier',
            '@biomejs/biome' => 'Biome',
            'stylelint' => 'Stylelint',
        ],
    ];

    /** Category for first-party atannex/* packages, which are always listed. */
    public const FIRST_PARTY = 'First-party';

    /**
     * Labels present in $dependencies, in catalog order, without duplicates.
     *
     * @param  array<string, array<string, string>>  $catalog
     * @param  array<string, mixed>  $dependencies  package name => anything
     * @return array<string, string> label => the first matching package name
     */
    public static function match(array $catalog, array $dependencies): array
    {
        $found = [];

        foreach ($catalog as $entries) {
            foreach ($entries as $key => $label) {
                if (isset($found[$label])) {
                    continue;
                }

                $package = self::find($key, $dependencies);
                if ($package !== null) {
                    $found[$label] = $package;
                }
            }
        }

        return $found;
    }

    /**
     * Group labels by their catalog category, keeping catalog order.
     * Labels the catalog doesn't know go under FIRST_PARTY.
     *
     * @template T
     *
     * @param  array<string, array<string, string>>  $catalog
     * @param  array<string, T>  $items  label => value
     * @return array<string, array<string, T>> category => [label => value]
     */
    public static function group(array $catalog, array $items): array
    {
        $grouped = [];

        foreach ($catalog as $category => $entries) {
            foreach (array_unique(array_values($entries)) as $label) {
                if (array_key_exists($label, $items)) {
                    $grouped[$category][$label] = $items[$label];
                    unset($items[$label]);
                }
            }
        }

        if ($items !== []) {
            $grouped[self::FIRST_PARTY] = $items;
        }

        return $grouped;
    }

    /** @param  array<string, mixed>  $dependencies */
    private static function find(string $key, array $dependencies): ?string
    {
        if (! str_ends_with($key, '/')) {
            return array_key_exists($key, $dependencies) ? $key : null;
        }

        foreach (array_keys($dependencies) as $package) {
            if (str_starts_with((string) $package, $key)) {
                return (string) $package;
            }
        }

        return null;
    }
}
