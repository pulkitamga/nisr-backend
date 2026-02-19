<?php

namespace App\Console\Commands;

use App\Models\BusinessSetting;
use App\Models\Translation;
use Illuminate\Console\Command;

class SeedUiTranslationEntries extends Command
{
    protected $signature = 'translations:seed-ui 
                            {--locale=* : Locale(s) to seed, e.g. --locale=ar}
                            {--key=* : Only seed specific key(s)}';

    protected $description = 'Seed missing UI translation rows from resources/lang files into DB';

    public function handle(): int
    {
        $setting = BusinessSetting::where('type', 'ui_translation_messages')->first();
        if (!$setting) {
            $this->error('ui_translation_messages setting was not found.');
            return self::FAILURE;
        }

        $keyMap = json_decode($setting->value ?? '[]', true);
        if (!is_array($keyMap) || empty($keyMap)) {
            $this->warn('No UI translation keys found in ui_translation_messages.');
            return self::SUCCESS;
        }

        $keys = array_keys($keyMap);
        $requestedKeys = collect((array)$this->option('key'))
            ->filter(fn($item) => is_string($item) && trim($item) !== '')
            ->map(fn($item) => trim($item))
            ->values()
            ->all();

        if (!empty($requestedKeys)) {
            $keys = array_values(array_intersect($keys, $requestedKeys));
        }

        if (empty($keys)) {
            $this->warn('No matching keys to seed.');
            return self::SUCCESS;
        }

        $locales = $this->resolveLocales();
        if (empty($locales)) {
            $this->warn('No locales available to seed.');
            return self::SUCCESS;
        }

        $totalCreated = 0;
        $totalSkipped = 0;

        foreach ($locales as $locale) {
            $created = 0;
            $skipped = 0;

            foreach ($keys as $key) {
                $exists = Translation::where('translationable_type', BusinessSetting::class)
                    ->where('translationable_id', $setting->id)
                    ->where('locale', $locale)
                    ->where('key', $key)
                    ->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                $value = getFallbackTranslationFromLegacyFiles(local: $locale, key: $key);
                if (empty($value)) {
                    $skipped++;
                    continue;
                }

                Translation::create([
                    'translationable_type' => BusinessSetting::class,
                    'translationable_id' => $setting->id,
                    'locale' => $locale,
                    'key' => $key,
                    'value' => $value,
                ]);
                $created++;
            }

            $totalCreated += $created;
            $totalSkipped += $skipped;

            $this->info("Locale {$locale}: created={$created}, skipped={$skipped}");
        }

        $this->info("Done. Total created={$totalCreated}, total skipped={$totalSkipped}");

        return self::SUCCESS;
    }

    private function resolveLocales(): array
    {
        $requestedLocales = collect((array)$this->option('locale'))
            ->filter(fn($item) => is_string($item) && trim($item) !== '')
            ->map(fn($item) => strtolower(trim($item)))
            ->values()
            ->all();

        if (!empty($requestedLocales)) {
            return array_values(array_unique(array_filter($requestedLocales, fn($item) => $item !== 'en')));
        }

        $languageConfig = getWebConfig('language');
        if (!is_array($languageConfig)) {
            return [];
        }

        $locales = [];
        foreach ($languageConfig as $item) {
            if (!is_array($item) || !array_key_exists('code', $item)) {
                continue;
            }

            $code = strtolower((string)$item['code']);
            if ($code !== '' && $code !== 'en') {
                $locales[] = $code;
            }
        }

        return array_values(array_unique($locales));
    }
}

