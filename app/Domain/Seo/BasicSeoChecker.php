<?php

namespace App\Domain\Seo;

use Illuminate\Support\Facades\Http;
use Throwable;

class BasicSeoChecker implements SeoCheckerInterface
{
    public function check(string $url): array
    {
        try {
            $response = Http::timeout(10)->get($url);
            $html = $response->body();
            preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $title);
            preg_match('/<meta[^>]+name=["\']description["\'][^>]+content=["\']([^"\']*)/i', $html, $description);

            return [
                'status_code' => $response->status(),
                'title' => trim($title[1] ?? ''),
                'meta_description' => trim($description[1] ?? ''),
                'robots_found' => Http::timeout(10)->get(rtrim($url, '/') . '/robots.txt')->successful(),
                'sitemap_found' => Http::timeout(10)->get(rtrim($url, '/') . '/sitemap.xml')->successful(),
                'redirects' => $response->redirectHistory(),
            ];
        } catch (Throwable $exception) {
            return ['status_code' => null, 'error' => $exception->getMessage()];
        }
    }
}
