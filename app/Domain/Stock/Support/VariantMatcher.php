<?php

namespace App\Domain\Stock\Support;

class VariantMatcher
{
    private VariantCanonicalizer $canonicalizer;

    public function __construct(?VariantCanonicalizer $canonicalizer = null)
    {
        $this->canonicalizer = $canonicalizer ?? new VariantCanonicalizer();
    }

    public function canonical(mixed $variant): ?string
    {
        return $this->canonicalizer->canonical($variant);
    }

    public function signatures(mixed $variant): array
    {
        return $this->canonicalizer->signatures($variant);
    }

    public function isDefault(mixed $variant): bool
    {
        return count($this->signatures($variant)) === 0;
    }

    public function matches(mixed $left, mixed $right): bool
    {
        $leftSignatures = $this->signatures($left);
        $rightSignatures = $this->signatures($right);

        if (empty($leftSignatures) || empty($rightSignatures)) {
            return empty($leftSignatures) && empty($rightSignatures);
        }

        $leftComposite = array_values(array_filter($leftSignatures, fn($signature) => $this->isCompositeSignature((string)$signature)));
        $rightComposite = array_values(array_filter($rightSignatures, fn($signature) => $this->isCompositeSignature((string)$signature)));

        if (!empty($leftComposite) && !empty($rightComposite)) {
            return count(array_intersect($leftComposite, $rightComposite)) > 0;
        }

        return ($leftSignatures[0] ?? null) === ($rightSignatures[0] ?? null);
    }

    private function isCompositeSignature(string $signature): bool
    {
        return str_contains($signature, '-') || str_contains($signature, ':') || str_contains($signature, '|');
    }

    public function canonicalFromProduct(mixed $variant, mixed $productVariationPayload): ?string
    {
        $variantSignatures = $this->signatures($variant);
        if (empty($variantSignatures)) {
            return null;
        }

        $variationRows = $this->decodeVariationRows($productVariationPayload);
        if (empty($variationRows)) {
            return $variantSignatures[0] ?? null;
        }

        foreach ($variationRows as $row) {
            $rowType = $row['type'] ?? null;
            if ($this->matches($variant, $rowType)) {
                return $this->canonical($rowType) ?? ($variantSignatures[0] ?? null);
            }
        }

        foreach ($variationRows as $row) {
            $rowType = $row['type'] ?? null;
            $rowSignatures = $this->signatures($rowType);
            if (count(array_intersect($variantSignatures, $rowSignatures)) > 0) {
                return $this->canonical($rowType) ?? ($variantSignatures[0] ?? null);
            }
        }

        return $variantSignatures[0] ?? null;
    }

    public function findVariationQty(mixed $productVariationPayload, mixed $variant): ?int
    {
        $variationRows = $this->decodeVariationRows($productVariationPayload);
        if (empty($variationRows)) {
            return null;
        }

        foreach ($variationRows as $row) {
            $rowType = $row['type'] ?? null;
            if ($this->matches($variant, $rowType)) {
                return isset($row['qty']) ? (int)$row['qty'] : null;
            }
        }

        return null;
    }

    public function decodeVariationRows(mixed $variationPayload): array
    {
        if (is_null($variationPayload)) {
            return [];
        }

        if (is_string($variationPayload)) {
            $decoded = json_decode($variationPayload, true);
            if (!is_array($decoded)) {
                return [];
            }
            $variationPayload = $decoded;
        }

        if (is_array($variationPayload)) {
            $rows = [];
            foreach ($variationPayload as $row) {
                if (is_array($row)) {
                    $rows[] = $row;
                    continue;
                }

                if (is_object($row)) {
                    $rows[] = [
                        'type' => $row->type ?? null,
                        'qty' => $row->qty ?? null,
                        'price' => $row->price ?? null,
                    ];
                }
            }
            return $rows;
        }

        return [];
    }
}
