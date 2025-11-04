<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    public function run(): void
    {
        $languages = [
            [
                'code' => 'en',
                'name' => 'English',
                'native_name' => 'English',
                'flag_emoji' => '🇬🇧',
                'is_rtl' => false,
                'is_active' => true,
                'is_default' => true,
                'sort_order' => 1,
            ],
            [
                'code' => 'ro',
                'name' => 'Romanian',
                'native_name' => 'Română',
                'flag_emoji' => '🇷🇴',
                'is_rtl' => false,
                'is_active' => true,
                'is_default' => false,
                'sort_order' => 2,
            ],
            [
                'code' => 'fr',
                'name' => 'French',
                'native_name' => 'Français',
                'flag_emoji' => '🇫🇷',
                'is_rtl' => false,
                'is_active' => true,
                'is_default' => false,
                'sort_order' => 3,
            ],
            [
                'code' => 'de',
                'name' => 'German',
                'native_name' => 'Deutsch',
                'flag_emoji' => '🇩🇪',
                'is_rtl' => false,
                'is_active' => true,
                'is_default' => false,
                'sort_order' => 4,
            ],
            [
                'code' => 'es',
                'name' => 'Spanish',
                'native_name' => 'Español',
                'flag_emoji' => '🇪🇸',
                'is_rtl' => false,
                'is_active' => true,
                'is_default' => false,
                'sort_order' => 5,
            ],
            [
                'code' => 'it',
                'name' => 'Italian',
                'native_name' => 'Italiano',
                'flag_emoji' => '🇮🇹',
                'is_rtl' => false,
                'is_active' => false,
                'is_default' => false,
                'sort_order' => 6,
            ],
            [
                'code' => 'ar',
                'name' => 'Arabic',
                'native_name' => 'العربية',
                'flag_emoji' => '🇸🇦',
                'is_rtl' => true,
                'is_active' => false,
                'is_default' => false,
                'sort_order' => 7,
            ],
            [
                'code' => 'he',
                'name' => 'Hebrew',
                'native_name' => 'עברית',
                'flag_emoji' => '🇮🇱',
                'is_rtl' => true,
                'is_active' => false,
                'is_default' => false,
                'sort_order' => 8,
            ],
        ];

        foreach ($languages as $language) {
            \App\Models\Language::updateOrCreate(
                ['code' => $language['code']],
                $language
            );
        }
    }
}
