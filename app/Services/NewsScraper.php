<?php

namespace App\Services;

use App\Models\Article;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Source-agnostic news aggregator.
 *
 * Two modes, chosen per-source in config/services.php:
 *   'rss'  -> parse a standard RSS/Atom feed (recommended, ToS-friendly)
 *   'html' -> scrape a listing page using CSS selectors
 *
 * IMPORTANT: scraping NewsNow directly violates their Terms of Service.
 * Prefer RSS feeds from the original publishers or the official LFC feed.
 * Only stores a headline + excerpt + link — the full article is rewritten
 * by Gemini before anything is published.
 */
class NewsScraper
{
    /** @return int number of new articles stored */
    public function run(): int
    {
        $stored = 0;

        foreach (config('services.news_sources', []) as $source) {
            if (empty($source['enabled'])) {
                continue;
            }

            try {
                $items = ($source['mode'] ?? 'rss') === 'html'
                    ? $this->scrapeHtml($source)
                    : $this->scrapeRss($source);

                foreach ($items as $item) {
                    $stored += $this->store($item, $source['name'] ?? 'source') ? 1 : 0;
                }
            } catch (\Throwable $e) {
                Log::warning('News source failed: ' . ($source['name'] ?? '?'), [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $stored;
    }

    private function client()
    {
        return Http::timeout(30)
            ->withUserAgent(config('services.scraper.user_agent'))
            ->retry(2, 1000);
    }

    private function scrapeRss(array $source): array
    {
        $body = $this->client()->get($source['url'])->body();

        $xml = @simplexml_load_string($body);
        if (! $xml) {
            return [];
        }

        // Handle both RSS (channel->item) and Atom (entry).
        $nodes = $xml->channel->item ?? $xml->entry ?? [];
        $items = [];

        foreach ($nodes as $node) {
            $link = (string) ($node->link['href'] ?? $node->link ?? '');
            $items[] = [
                'title'   => trim((string) $node->title),
                'url'     => trim($link),
                'excerpt' => strip_tags(trim((string) ($node->description ?? $node->summary ?? ''))),
                'image'   => $this->extractRssImage($node),
            ];
        }

        return array_slice($items, 0, $source['limit'] ?? 20);
    }

    private function extractRssImage($node): ?string
    {
        $media = $node->children('http://search.yahoo.com/mrss/');
        if (isset($media->content) && $media->content->attributes()['url']) {
            return (string) $media->content->attributes()['url'];
        }
        if (isset($node->enclosure['url'])) {
            return (string) $node->enclosure['url'];
        }
        return null;
    }

    private function scrapeHtml(array $source): array
    {
        $html = $this->client()->get($source['url'])->body();
        $crawler = new Crawler($html);
        $sel = $source['selectors'];

        $items = [];
        $crawler->filter($sel['item'])->each(function (Crawler $node) use ($sel, &$items, $source) {
            try {
                $linkNode = $node->filter($sel['link']);
                $url = $linkNode->attr('href');
                if ($url && ! str_starts_with($url, 'http')) {
                    $url = rtrim($source['base_url'] ?? '', '/') . '/' . ltrim($url, '/');
                }
                $items[] = [
                    'title'   => trim($node->filter($sel['title'])->text('')),
                    'url'     => $url,
                    'excerpt' => isset($sel['excerpt']) && $node->filter($sel['excerpt'])->count()
                        ? trim($node->filter($sel['excerpt'])->text('')) : null,
                    'image'   => isset($sel['image']) && $node->filter($sel['image'])->count()
                        ? $node->filter($sel['image'])->attr('src') : null,
                ];
            } catch (\Throwable $e) {
                // skip malformed row
            }
        });

        return array_slice($items, 0, $source['limit'] ?? 20);
    }

    private function store(array $item, string $sourceName): bool
    {
        if (empty($item['url']) || empty($item['title'])) {
            return false;
        }

        // Dedupe on source_url (unique). firstOrCreate avoids re-storing.
        $article = Article::firstOrCreate(
            ['source_url' => $item['url']],
            [
                'source_name'      => $sourceName,
                'original_title'   => mb_substr($item['title'], 0, 500),
                'original_excerpt' => $item['excerpt'] ? mb_substr($item['excerpt'], 0, 1000) : null,
                'original_content' => $item['excerpt'] ?? $item['title'],
                'image_url'        => $item['image'] ?? null,
                'status'           => 'scraped',
                'scraped_at'       => now(),
            ]
        );

        return $article->wasRecentlyCreated;
    }
}
