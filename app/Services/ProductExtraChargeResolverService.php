<?php

namespace App\Services;

use App\Models\ManageExtraCharge;
use Illuminate\Support\Collection;

class ProductExtraChargeResolverService
{
    private const SUPPORTED_TYPES = ['exchange', 'installation'];

    public function resolveForProduct(object $product): array
    {
        return $this->resolveByCategoryHierarchy(
            categoryId: (int)($product->category_id ?? 0),
            subCategoryId: (int)($product->sub_category_id ?? 0),
            subSubCategoryId: (int)($product->sub_sub_category_id ?? 0)
        );
    }

    public function resolveByCategoryHierarchy(?int $categoryId, ?int $subCategoryId, ?int $subSubCategoryId): array
    {
        $hierarchyCategoryIds = $this->buildHierarchyCategoryIds(
            categoryId: $categoryId,
            subCategoryId: $subCategoryId,
            subSubCategoryId: $subSubCategoryId
        );

        if (empty($hierarchyCategoryIds)) {
            return $this->emptyCharges();
        }

        $candidateCharges = ManageExtraCharge::query()
            ->select(['id', 'category_id', 'type', 'charges'])
            ->whereIn('type', self::SUPPORTED_TYPES)
            ->where('status', 1)
            ->whereIn('category_id', $hierarchyCategoryIds)
            ->orderByDesc('id')
            ->get();

        return $this->resolveFromCandidates($hierarchyCategoryIds, $candidateCharges);
    }

    public function resolveFromCandidates(array $hierarchyCategoryIds, Collection $candidateCharges): array
    {
        $normalizedHierarchyIds = $this->normalizeHierarchyCategoryIds($hierarchyCategoryIds);
        $resolvedCharges = $this->emptyCharges();

        if (empty($normalizedHierarchyIds) || $candidateCharges->isEmpty()) {
            return $resolvedCharges;
        }

        $chargeLookup = [];
        foreach ($candidateCharges as $charge) {
            $chargeType = (string)($charge->type ?? '');
            $categoryId = (int)($charge->category_id ?? 0);
            $chargeValue = (float)($charge->charges ?? 0);

            if (!in_array($chargeType, self::SUPPORTED_TYPES, true) || $categoryId <= 0 || $chargeValue <= 0) {
                continue;
            }

            $lookupKey = $this->makeLookupKey($categoryId, $chargeType);
            if (!array_key_exists($lookupKey, $chargeLookup)) {
                $chargeLookup[$lookupKey] = $chargeValue;
            }
        }

        foreach (self::SUPPORTED_TYPES as $chargeType) {
            foreach ($normalizedHierarchyIds as $categoryId) {
                $lookupKey = $this->makeLookupKey($categoryId, $chargeType);
                if (array_key_exists($lookupKey, $chargeLookup)) {
                    $resolvedCharges[$chargeType] = $chargeLookup[$lookupKey];
                    break;
                }
            }
        }

        return $resolvedCharges;
    }

    public function buildHierarchyCategoryIds(?int $categoryId, ?int $subCategoryId, ?int $subSubCategoryId): array
    {
        return $this->normalizeHierarchyCategoryIds([$subSubCategoryId, $subCategoryId, $categoryId]);
    }

    private function normalizeHierarchyCategoryIds(array $categoryIds): array
    {
        return array_values(array_unique(array_filter(array_map(
            fn($id) => (int)$id,
            $categoryIds
        ), fn($id) => $id > 0)));
    }

    private function makeLookupKey(int $categoryId, string $chargeType): string
    {
        return $categoryId . ':' . $chargeType;
    }

    private function emptyCharges(): array
    {
        return [
            'exchange' => 0.0,
            'installation' => 0.0,
        ];
    }
}
