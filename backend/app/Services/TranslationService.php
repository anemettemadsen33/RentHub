<?php

namespace App\Services;

use App\Models\Translation;
use App\Models\SupportedLanguage;
use Illuminate\Support\Facades\Cache;

class TranslationService
{
    public function getTranslation(string $key, string $locale = null, string $group = 'common', $default = null)
    {
        return Translation::get($key, $locale, $group, $default);
    }

    public function getAllTranslations(string $locale, string $group = null): array
    {
        $cacheKey = $group 
            ? "translations.{$locale}.{$group}"
            : "translations.{$locale}.all";

        return Cache::remember($cacheKey, 3600, function () use ($locale, $group) {
            return Translation::getAllByLocale($locale, $group);
        });
    }

    public function setTranslation(string $locale, string $group, string $key, string $value): Translation
    {
        return Translation::updateOrCreate(
            [
                'locale' => $locale,
                'group' => $group,
                'key' => $key,
            ],
            [
                'value' => $value,
            ]
        );
    }

    public function deleteTranslation(string $locale, string $group, string $key): bool
    {
        return Translation::where('locale', $locale)
            ->where('group', $group)
            ->where('key', $key)
            ->delete() > 0;
    }

    public function getSupportedLanguages(bool $activeOnly = true): array
    {
        $query = SupportedLanguage::query();
        
        if ($activeOnly) {
            $query->active();
        }

        return $query->ordered()->get()->toArray();
    }

    public function detectLanguage(string $acceptLanguage): string
    {
        $supported = Cache::remember('supported_language_codes', 3600, function () {
            return SupportedLanguage::active()->pluck('code')->toArray();
        });

        $locales = $this->parseAcceptLanguage($acceptLanguage);

        foreach ($locales as $locale) {
            if (in_array($locale, $supported)) {
                return $locale;
            }
        }

        return config('app.locale', 'en');
    }

    protected function parseAcceptLanguage(string $acceptLanguage): array
    {
        $locales = [];
        
        foreach (explode(',', $acceptLanguage) as $lang) {
            $parts = explode(';', $lang);
            $locale = trim($parts[0]);
            
            if (strlen($locale) >= 2) {
                $locales[] = substr($locale, 0, 2);
            }
        }

        return array_unique($locales);
    }

    public function importTranslations(string $locale, array $translations, string $group = 'common'): int
    {
        $count = 0;

        foreach ($translations as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $subKey => $subValue) {
                    $this->setTranslation($locale, $group, "{$key}.{$subKey}", $subValue);
                    $count++;
                }
            } else {
                $this->setTranslation($locale, $group, $key, $value);
                $count++;
            }
        }

        return $count;
    }

    public function exportTranslations(string $locale, string $group = null): array
    {
        return $this->getAllTranslations($locale, $group);
    }
}
