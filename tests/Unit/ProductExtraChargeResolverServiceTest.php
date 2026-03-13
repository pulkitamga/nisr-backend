<?php

namespace Tests\Unit;

use App\Services\ProductExtraChargeResolverService;
use Illuminate\Support\Collection;
use Tests\TestCase;

class ProductExtraChargeResolverServiceTest extends TestCase
{
    public function test_it_uses_category_values_when_child_levels_are_not_configured(): void
    {
        $service = new ProductExtraChargeResolverService();
        $hierarchy = $service->buildHierarchyCategoryIds(categoryId: 8, subCategoryId: null, subSubCategoryId: null);

        $resolved = $service->resolveFromCandidates($hierarchy, $this->chargesCollection([
            ['category_id' => 8, 'type' => 'installation', 'charges' => 150],
            ['category_id' => 8, 'type' => 'exchange', 'charges' => 20],
        ]));

        $this->assertSame(150.0, $resolved['installation']);
        $this->assertSame(20.0, $resolved['exchange']);
    }

    public function test_it_prefers_subcategory_over_category(): void
    {
        $service = new ProductExtraChargeResolverService();
        $hierarchy = $service->buildHierarchyCategoryIds(categoryId: 8, subCategoryId: 9, subSubCategoryId: null);

        $resolved = $service->resolveFromCandidates($hierarchy, $this->chargesCollection([
            ['category_id' => 8, 'type' => 'installation', 'charges' => 150],
            ['category_id' => 9, 'type' => 'installation', 'charges' => 300],
            ['category_id' => 8, 'type' => 'exchange', 'charges' => 20],
            ['category_id' => 9, 'type' => 'exchange', 'charges' => 50],
        ]));

        $this->assertSame(300.0, $resolved['installation']);
        $this->assertSame(50.0, $resolved['exchange']);
    }

    public function test_it_prefers_sub_subcategory_over_subcategory_and_category(): void
    {
        $service = new ProductExtraChargeResolverService();
        $hierarchy = $service->buildHierarchyCategoryIds(categoryId: 8, subCategoryId: 9, subSubCategoryId: 10);

        $resolved = $service->resolveFromCandidates($hierarchy, $this->chargesCollection([
            ['category_id' => 8, 'type' => 'installation', 'charges' => 150],
            ['category_id' => 9, 'type' => 'installation', 'charges' => 300],
            ['category_id' => 10, 'type' => 'installation', 'charges' => 450],
            ['category_id' => 8, 'type' => 'exchange', 'charges' => 20],
            ['category_id' => 9, 'type' => 'exchange', 'charges' => 50],
            ['category_id' => 10, 'type' => 'exchange', 'charges' => 100],
        ]));

        $this->assertSame(450.0, $resolved['installation']);
        $this->assertSame(100.0, $resolved['exchange']);
    }

    public function test_it_resolves_each_charge_type_independently_from_nearest_available_level(): void
    {
        $service = new ProductExtraChargeResolverService();
        $hierarchy = $service->buildHierarchyCategoryIds(categoryId: 8, subCategoryId: 9, subSubCategoryId: 10);

        $resolved = $service->resolveFromCandidates($hierarchy, $this->chargesCollection([
            ['category_id' => 8, 'type' => 'installation', 'charges' => 150],
            ['category_id' => 9, 'type' => 'exchange', 'charges' => 50],
            ['category_id' => 10, 'type' => 'exchange', 'charges' => 100],
        ]));

        $this->assertSame(150.0, $resolved['installation']);
        $this->assertSame(100.0, $resolved['exchange']);
    }

    public function test_it_returns_zero_when_no_active_or_configured_values_exist(): void
    {
        $service = new ProductExtraChargeResolverService();
        $hierarchy = $service->buildHierarchyCategoryIds(categoryId: 8, subCategoryId: 9, subSubCategoryId: 10);

        $resolved = $service->resolveFromCandidates($hierarchy, $this->chargesCollection([
            ['category_id' => 10, 'type' => 'installation', 'charges' => 0],
            ['category_id' => 9, 'type' => 'exchange', 'charges' => 0],
        ]));

        $this->assertSame(0.0, $resolved['installation']);
        $this->assertSame(0.0, $resolved['exchange']);
    }

    public function test_it_uses_latest_candidate_when_duplicate_rows_exist_for_same_level(): void
    {
        $service = new ProductExtraChargeResolverService();
        $hierarchy = $service->buildHierarchyCategoryIds(categoryId: 8, subCategoryId: 9, subSubCategoryId: null);

        $resolved = $service->resolveFromCandidates($hierarchy, $this->chargesCollection([
            ['category_id' => 9, 'type' => 'installation', 'charges' => 300],
            ['category_id' => 9, 'type' => 'installation', 'charges' => 250],
            ['category_id' => 8, 'type' => 'exchange', 'charges' => 20],
        ]));

        $this->assertSame(300.0, $resolved['installation']);
        $this->assertSame(20.0, $resolved['exchange']);
    }

    private function chargesCollection(array $rows): Collection
    {
        return collect($rows)->map(fn(array $row) => (object)$row);
    }
}
