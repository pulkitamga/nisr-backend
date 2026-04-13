<?php

namespace App\Support;

class WholesaleLinePrice
{
    public static function fromValues(
        float|int|string $basePrice,
        float|int|string $quantity,
        float|int|string|null $tax,
        float|int|string|null $storedFinalPrice = null
    ): array {
        $unitPrice = (float) $basePrice;
        $qty = (float) $quantity;
        $baseTotal = $unitPrice * $qty;

        $rawTax = trim((string) ($tax ?? '0'));
        $taxValue = (float) str_replace('%', '', $rawTax);
        $taxMode = self::resolveTaxMode($rawTax, $taxValue, $baseTotal, $storedFinalPrice);
        $taxAmount = $taxMode === 'percent'
            ? ($baseTotal * $taxValue) / 100
            : $taxValue;

        return [
            'base_total' => round($baseTotal, 2),
            'tax_value' => round($taxValue, 2),
            'tax_mode' => $taxMode,
            'tax_amount' => round($taxAmount, 2),
            'final_price' => round($baseTotal + $taxAmount, 2),
            'display_tax' => $taxMode === 'percent'
                ? self::formatDecimal($taxValue) . '%'
                : self::formatDecimal($taxValue),
        ];
    }

    private static function resolveTaxMode(
        string $rawTax,
        float $taxValue,
        float $baseTotal,
        float|int|string|null $storedFinalPrice = null
    ): string {
        if (str_contains($rawTax, '%')) {
            return 'percent';
        }

        if ($taxValue <= 100) {
            $storedFinal = is_null($storedFinalPrice) ? null : (float) $storedFinalPrice;
            $percentTotal = $baseTotal + (($baseTotal * $taxValue) / 100);

            if (
                is_null($storedFinal)
                || abs($storedFinal - $baseTotal) < 0.01
                || abs($storedFinal - $percentTotal) < 0.01
            ) {
                return 'percent';
            }
        }

        return 'amount';
    }

    private static function formatDecimal(float $value): string
    {
        return number_format($value, 2, '.', '');
    }
}
