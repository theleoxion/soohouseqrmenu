<?php

namespace App\Helpers;

class TurkishSlug
{
    /**
     * Convert any Turkish string into a clean URL-safe ASCII slug.
     */
    public static function make(string $text): string
    {
        $map = [
            'ç' => 'c', 'Ç' => 'c',
            'ğ' => 'g', 'Ğ' => 'g',
            'ı' => 'i', 'I' => 'i',
            'İ' => 'i', 'i' => 'i',
            'ö' => 'o', 'Ö' => 'o',
            'ş' => 's', 'Ş' => 's',
            'ü' => 'u', 'Ü' => 'u',
        ];

        $converted = strtr($text, $map);
        $slug = strtolower(trim($converted));
        $slug = preg_replace('/[^a-z0-9-]+/', '-', $slug) ?? '';
        $slug = preg_replace('/-+/', '-', $slug) ?? '';

        return trim($slug, '-');
    }
}
