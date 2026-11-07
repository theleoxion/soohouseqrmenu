<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\SeoPage;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    public function sitemap(): Response
    {
        $branches = Branch::where('is_active', true)->orderBy('sort_order')->get();
        $seoPages = SeoPage::where('noindex', false)->get()->keyBy('route_name');

        $urls = [
            ['loc' => route('public.home'), 'priority' => '1.0', 'changefreq' => 'daily'],
            ['loc' => route('public.about'), 'priority' => '0.8', 'changefreq' => 'monthly'],
            ['loc' => route('public.products'), 'priority' => '0.9', 'changefreq' => 'weekly'],
            ['loc' => route('public.branches'), 'priority' => '0.9', 'changefreq' => 'weekly'],
            ['loc' => route('public.franchise'), 'priority' => '0.9', 'changefreq' => 'monthly'],
            ['loc' => route('public.press'), 'priority' => '0.7', 'changefreq' => 'weekly'],
            ['loc' => route('public.contact'), 'priority' => '0.8', 'changefreq' => 'monthly'],
        ];

        foreach ($branches as $branch) {
            $urls[] = [
                'loc' => route('public.branch.show', $branch->slug),
                'lastmod' => $branch->updated_at?->toAtomString(),
                'priority' => '0.9',
                'changefreq' => 'weekly',
            ];
            $urls[] = [
                'loc' => route('qrmenu.show', $branch->slug),
                'lastmod' => $branch->updated_at?->toAtomString(),
                'priority' => '0.8',
                'changefreq' => 'daily',
            ];
        }

        $xml = view('public.sitemap', compact('urls'))->render();

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    public function robots(): Response
    {
        $sitemapUrl = route('seo.sitemap');
        $content = implode("\n", [
            'User-agent: *',
            'Allow: /',
            'Disallow: /yonetim',
            'Disallow: /yonetim/',
            'Disallow: /qrmenu/*/m/*',
            '',
            "Sitemap: {$sitemapUrl}",
        ]);

        return response($content, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
