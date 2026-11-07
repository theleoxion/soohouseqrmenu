<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SeoPage extends Model
{
    protected $fillable = [
        'route_name',
        'page_label',
        'meta_title',
        'meta_description',
        'og_title',
        'og_description',
        'og_image',
        'canonical_url',
        'noindex',
    ];

    protected function casts(): array
    {
        return [
            'noindex' => 'boolean',
        ];
    }

    public static function forRoute(string $routeName): ?self
    {
        try {
            $pages = Cache::rememberForever('soohouse_seo_pages', function () {
                return self::all()->keyBy('route_name');
            });

            return $pages->get($routeName);
        } catch (\Throwable) {
            return null;
        }
    }

    public static function clearCache(): void
    {
        Cache::forget('soohouse_seo_pages');
    }
}
