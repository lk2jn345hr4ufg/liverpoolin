<?php

namespace App\Console\Commands;

use App\Services\NewsScraper;
use Illuminate\Console\Command;

class ScrapeNews extends Command
{
    protected $signature = 'scrape:news';
    protected $description = 'Aggregate Liverpool news from configured sources (stored as unedited drafts).';

    public function handle(NewsScraper $scraper): int
    {
        $this->info('Scraping news sources...');
        $new = $scraper->run();
        $this->info("Done. {$new} new article(s) stored as drafts (status=scraped).");
        return self::SUCCESS;
    }
}
