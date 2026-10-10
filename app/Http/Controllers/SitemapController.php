<?php

namespace App\Http\Controllers;

use App\Support\SitemapBuilder;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $latest = SitemapBuilder::latestPostModified();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
        $xml .= $this->sitemapRow(route('sitemap.pages'));
        $xml .= $this->sitemapRow(route('sitemap.posts'), $latest);
        $xml .= '</sitemapindex>';

        return $this->xml($xml);
    }

    public function pages(): Response
    {
        return $this->xml($this->urlset(SitemapBuilder::pages()));
    }

    public function posts(): Response
    {
        return $this->xml($this->urlset(SitemapBuilder::posts()));
    }

    /**
     * @param  list<array{loc: string, lastmod?: string, changefreq?: string, priority?: string}>  $urls
     */
    private function urlset(array $urls): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        foreach ($urls as $url) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>'.e($url['loc'])."</loc>\n";
            if (! empty($url['lastmod'])) {
                $xml .= '    <lastmod>'.e($url['lastmod'])."</lastmod>\n";
            }
            $xml .= '    <changefreq>'.e($url['changefreq'] ?? 'weekly')."</changefreq>\n";
            $xml .= '    <priority>'.e($url['priority'] ?? '0.5')."</priority>\n";
            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';

        return $xml;
    }

    private function sitemapRow(string $loc, ?string $lastmod = null): string
    {
        $xml = "  <sitemap>\n";
        $xml .= '    <loc>'.e($loc)."</loc>\n";
        if ($lastmod) {
            $xml .= '    <lastmod>'.e($lastmod)."</lastmod>\n";
        }
        $xml .= "  </sitemap>\n";

        return $xml;
    }

    private function xml(string $xml): Response
    {
        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=0, must-revalidate',
        ]);
    }
}
