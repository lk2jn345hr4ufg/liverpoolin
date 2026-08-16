<?php

namespace App\Services;

use App\Models\Fixture;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Scrapes the Liverpool FC fixtures page.
 *
 * NOTE: liverpoolfc.com is a JavaScript-rendered site whose markup changes.
 * The selectors below live in config/services.php ('fixtures') so you can
 * update them without touching this class. If the page is fully client-side
 * rendered you may need a headless browser (see README) or an official
 * fixtures API/feed instead.
 */
class FixtureScraper
{
    public function run(): int
    {
        $cfg = config('services.fixtures');
        $count = 0;

        try {
            $html = Http::timeout(30)
                ->withUserAgent(config('services.scraper.user_agent'))
                ->retry(2, 1000)
                ->get($cfg['url'])
                ->body();

            $crawler = new Crawler($html);
            $sel = $cfg['selectors'];

            $crawler->filter($sel['fixture'])->each(function (Crawler $node) use ($sel, &$count) {
                try {
                    $home = trim($node->filter($sel['home'])->text(''));
                    $away = trim($node->filter($sel['away'])->text(''));
                    $when = $node->filter($sel['datetime'])->count()
                        ? $node->filter($sel['datetime'])->attr('datetime')
                        : null;

                    if (! $home || ! $away) {
                        return;
                    }

                    $kickoff = $when ? Carbon::parse($when) : null;

                    Fixture::updateOrCreate(
                        ['fingerprint' => Fixture::makeFingerprint($home, $away, $kickoff?->toIso8601String())],
                        [
                            'competition' => isset($sel['competition']) && $node->filter($sel['competition'])->count()
                                ? trim($node->filter($sel['competition'])->text('')) : null,
                            'home_team'   => $home,
                            'away_team'   => $away,
                            'venue'       => isset($sel['venue']) && $node->filter($sel['venue'])->count()
                                ? trim($node->filter($sel['venue'])->text('')) : null,
                            'kickoff_at'  => $kickoff,
                            'status'      => 'scheduled',
                        ]
                    );
                    $count++;
                } catch (\Throwable $e) {
                    // skip malformed fixture row
                }
            });
        } catch (\Throwable $e) {
            Log::warning('Fixture scrape failed', ['error' => $e->getMessage()]);
        }

        return $count;
    }
}
