<?php

namespace App\Domain\Stock\Support;

class VariantCanonicalizer
{
    public function canonical(mixed $variant): ?string
    {
        $signatures = $this->signatures($variant);
        return $signatures[0] ?? null;
    }

    public function signatures(mixed $variant): array
    {
        $raw = $this->resolveRawVariant($variant);
        if ($raw === null) {
            return [];
        }

        $signatures = [];
        $baseNormalized = $this->normalizeScalar($raw);
        if ($baseNormalized !== '') {
            $signatures[] = $baseNormalized;
        }

        $pairs = $this->parseKeyValuePairs($raw);
        if (!empty($pairs)) {
            ksort($pairs);

            $pairParts = [];
            $pairValues = [];
            foreach ($pairs as $key => $value) {
                $normalizedKey = $this->normalizeKey($key);
                $normalizedValue = $this->normalizeScalar($value);
                if ($normalizedKey === '' || $normalizedValue === '') {
                    continue;
                }
                $pairParts[] = $normalizedKey . ':' . $normalizedValue;
                $pairValues[] = $normalizedValue;
            }

            if (!empty($pairParts)) {
                $signatures[] = implode('|', $pairParts);
            }

            foreach ($pairValues as $pairValue) {
                $signatures[] = $pairValue;
            }

            if (count($pairValues) > 1) {
                $joinedValues = implode('-', $pairValues);
                $signatures[] = $joinedValues;

                $sortedValues = $pairValues;
                sort($sortedValues);
                $signatures[] = implode('-', $sortedValues);
            }
        } elseif (str_contains($baseNormalized, '-')) {
            $segments = array_values(array_filter(explode('-', $baseNormalized), fn($segment) => $segment !== ''));
            if (count($segments) > 1) {
                $sortedSegments = $segments;
                sort($sortedSegments);
                $signatures[] = implode('-', $sortedSegments);
            }
        }

        return array_values(array_unique(array_filter($signatures)));
    }

    private function resolveRawVariant(mixed $variant): ?string
    {
        if (is_null($variant)) {
            return null;
        }

        if (is_array($variant)) {
            if (isset($variant['type'])) {
                return $this->resolveRawVariant($variant['type']);
            }

            $variant = json_encode($variant);
        }

        if (is_object($variant)) {
            $asArray = (array)$variant;
            if (isset($asArray['type'])) {
                return $this->resolveRawVariant($asArray['type']);
            }
            $variant = json_encode($asArray);
        }

        $raw = trim((string)$variant);
        if ($raw === '') {
            return null;
        }

        $lowerRaw = strtolower($raw);
        if (in_array($lowerRaw, ['null', 'default', '__default__', 'no variation', 'no_variation'], true)) {
            return null;
        }

        if (str_starts_with($raw, '{') || str_starts_with($raw, '[')) {
            $decoded = json_decode($raw, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                if (is_array($decoded) && isset($decoded['type'])) {
                    return $this->resolveRawVariant($decoded['type']);
                }

                if (is_array($decoded) && count($decoded) === 1) {
                    $firstValue = reset($decoded);
                    return $this->resolveRawVariant($firstValue);
                }
            }
        }

        return $raw;
    }

    private function parseKeyValuePairs(string $raw): array
    {
        if (!str_contains($raw, ':')) {
            return [];
        }

        $pairs = [];
        $segments = preg_split('/\|/', $raw) ?: [];
        foreach ($segments as $segment) {
            $segment = trim((string)$segment);
            if ($segment === '' || !str_contains($segment, ':')) {
                continue;
            }

            [$key, $value] = array_map('trim', explode(':', $segment, 2));
            if ($key === '' || $value === '') {
                continue;
            }

            $pairs[$key] = $value;
        }

        return $pairs;
    }

    private function normalizeKey(string $key): string
    {
        $normalized = strtolower(trim($key));
        $normalized = preg_replace('/\s+/', ' ', $normalized);
        return str_replace(' ', '_', $normalized);
    }

    private function normalizeScalar(string $value): string
    {
        $normalized = strtolower(trim($value));
        $normalized = preg_replace('/\s+/', '', $normalized);
        return $normalized;
    }
}
