<?php

namespace App\Console\Commands;

use App\Services\FixtureScraper;
use Illuminate\Console\Command;

class ScrapeFixtures extends Command
{
    protected $signature = 'scrape:fixtures';
    protected $description = 'Scrape the Liverpool FC fixtures/schedule page.';

    public function handle(FixtureScraper $scraper): int
    {
        $this->info('Scraping fixtures...');
        $count = $scraper->run();
        $this->info("Done. {$count} fixture(s) upserted.");
        return self::SUCCESS;
    }
}
