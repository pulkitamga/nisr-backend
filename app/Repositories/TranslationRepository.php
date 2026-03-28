<?php

namespace App\Repositories;

use App\Contracts\Repositories\TranslationRepositoryInterface;
use App\Models\Translation;

class TranslationRepository implements TranslationRepositoryInterface
{
    public function __construct(
        private readonly Translation $translation
    ) {}

    private function resolveDefaultLocale(object $request): string
    {
        $requestLanguages = collect(data_get($request, 'lang', []))
            ->filter(fn ($locale) => is_string($locale) && $locale !== '')
            ->values();

        // Use pnc_language (the business setting) as the authoritative default,
        // NOT config('app.locale') which can disagree with the form's $defaultLanguage.
        $configuredLocale = getConfiguredDefaultLanguage();

        if ($requestLanguages->contains($configuredLocale)) {
            return $configuredLocale;
        }

        return (string) ($requestLanguages->first() ?? 'en');
    }

    public function add(object $request, string $model, int|string $id): bool
    {
        $defaultLocale = $this->resolveDefaultLocale($request);

        $allowedFields = config('translation.translatable_fields');

        $translatableFields = collect($request->only($allowedFields))
            ->keys();

        foreach ($request->lang as $index => $key) {
            if ($key === $defaultLocale) continue;

            foreach ($translatableFields as $field) {
                if (!is_array($request[$field] ?? null)) {
                    continue;
                }
                if (isset($request[$field][$index])) {

                    $this->translation->insert([
                        'translationable_type' => $model,
                        'translationable_id' => $id,
                        'locale' => $key,
                        'key' => $field,
                        'value' => $request[$field][$index],
                    ]);
                }
            }
        }

        return true;
    }


    public function update(object $request, string $model, int|string $id): bool
    {
        $defaultLocale = $this->resolveDefaultLocale($request);

        $allowedFields = config('translation.translatable_fields');

        $translatableFields = collect($request->only($allowedFields))
            ->keys();

        foreach ($request->lang as $index => $key) {
            if ($key === $defaultLocale) continue;

            foreach ($translatableFields as $field) {
                if (isset($request[$field][$index])) {
                    $this->translation
                        ->where('translationable_type', $model)
                        ->where('translationable_id', $id)
                        ->where('locale', $key)
                        ->where('key', $field)
                        ->delete();

                    $this->translation->insert([
                        'translationable_type' => $model,
                        'translationable_id' => $id,
                        'locale' => $key,
                        'key' => $field,
                        'value' => $request[$field][$index],
                    ]);
                }
            }
        }

        return true;
    }

    public function updateData(string $model, string $id, string $lang, string $key, string $value): bool
    {
        $this->translation->updateOrInsert(
            [
                'translationable_type' => $model,
                'translationable_id' => $id,
                'locale' => $lang,
                'key' => $key
            ],
            [
                'value' => $value
            ]
        );
        return true;
    }
    public function delete(string $model, int|string $id): bool
    {
        $this->translation->where('translationable_type', $model)->where('translationable_id', $id)->delete();
        return true;
    }


    public function updateArrayBasedSectionTranslations($request, string $model, int $id): bool
    {
        $langs = $request->lang ?? [];
        $index = (int)$request->input('index'); 
        $defaultLocale = $this->resolveDefaultLocale($request);
        $allowedFields = config('translation.translatable_fields');

        $translatableFields = collect($request->only($allowedFields))
            ->keys()
            ->toArray();


        foreach ($langs as $langIndex => $locale) {
            if ($locale === $defaultLocale) continue;

            foreach ($translatableFields as $field) {
                $value = $request->input($field)[$langIndex] ?? null;

                if ($value !== null) {
                    Translation::updateOrCreate(
                        [
                            'translationable_type' => $model,
                            'translationable_id' => $id,
                            'locale' => $locale,
                            'key' => $field,
                            'item_index' => $index,
                        ],
                        [
                            'value' => $value,
                        ]
                    );
                }
            }
        }

        return true;
    }


    public function createArrayBasedSectionTranslations($request, string $model, int $id): bool
    {

        $langs = $request->lang ?? [];
        $index = (int) $request->input('index', -1);
        $defaultLocale = $this->resolveDefaultLocale($request);

        $allowedFields = config('translation.translatable_fields');

        $translatableFields = collect($request->only($allowedFields))
            ->keys()
            ->toArray();

        foreach ($langs as $langIndex => $locale) {
            if ($locale === $defaultLocale) continue;

            foreach ($translatableFields as $field) {
                $value = $request->input($field)[$langIndex] ?? null;

                if ($value !== null) {
                    Translation::create([
                        'translationable_type' => $model,
                        'translationable_id' => $id,
                        'locale' => $locale,
                        'key' => $field,
                        'value' => $value,
                        'item_index' => $index,
                    ]);
                }
            }
        }

        return true;
    }
}
