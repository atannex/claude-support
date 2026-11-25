<?php

declare(strict_types=1);

namespace Atannex\ClaudeSupport\Tests\Unit;

use Atannex\ClaudeSupport\Stack\Catalog;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CatalogTest extends TestCase
{
    /** @return array<string, array{array<string, array<string, string>>, string}> */
    public static function catalogs(): array
    {
        return [
            // Packagist: vendor/name, lowercase.
            'composer' => [Catalog::COMPOSER, '#^[a-z0-9]([_.-]?[a-z0-9]+)*/[a-z0-9](([_.]|-{1,2})?[a-z0-9]+)*$#'],
            // npm: optional @scope/, lowercase, URL-safe; or a bare "@scope/" prefix.
            'npm' => [Catalog::NPM, '#^(@[a-z0-9][a-z0-9._-]*/)?([a-z0-9][a-z0-9._-]*)?$#'],
        ];
    }

    /** @param  array<string, array<string, string>>  $catalog */
    #[DataProvider('catalogs')]
    public function test_every_key_is_a_well_formed_package_name(array $catalog, string $pattern): void
    {
        foreach ($catalog as $category => $entries) {
            foreach ($entries as $package => $label) {
                $this->assertMatchesRegularExpression($pattern, $package, "{$category}: {$package}");
                $this->assertNotSame('', $label);
            }
        }
    }

    /** @param  array<string, array<string, string>>  $catalog */
    #[DataProvider('catalogs')]
    public function test_packages_appear_once_and_labels_belong_to_one_category(array $catalog): void
    {
        $packages = [];
        $labels = [];

        foreach ($catalog as $category => $entries) {
            foreach ($entries as $package => $label) {
                $this->assertArrayNotHasKey($package, $packages, "{$package} is listed under {$category} and ".($packages[$package] ?? ''));
                $packages[$package] = $category;

                $this->assertSame($category, $labels[$label] ??= $category, "label {$label} is used in two categories");
            }
        }
    }

    public function test_scope_prefixes_match_any_package_in_the_scope(): void
    {
        $found = Catalog::match(Catalog::NPM, ['@radix-ui/react-dialog' => '^1', 'vue' => '^3']);

        $this->assertSame(['Vue' => 'vue', 'Radix UI' => '@radix-ui/react-dialog'], $found);
    }

    public function test_aliases_collapse_to_one_label(): void
    {
        $found = Catalog::match(Catalog::NPM, ['@laravel/echo-vue' => '^2', 'laravel-echo' => '^2']);

        $this->assertSame(['Laravel Echo' => 'laravel-echo'], $found);
    }

    public function test_grouping_keeps_catalog_order_and_files_unknowns_as_first_party(): void
    {
        $grouped = Catalog::group(Catalog::COMPOSER, ['Horizon' => '5.0', 'atannex/billing' => '1.0', 'Sanctum' => '4.0']);

        $this->assertSame([
            'Auth & access' => ['Sanctum' => '4.0'],
            'Infrastructure' => ['Horizon' => '5.0'],
            Catalog::FIRST_PARTY => ['atannex/billing' => '1.0'],
        ], $grouped);
    }
}
