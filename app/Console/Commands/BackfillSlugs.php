<?php

namespace App\Console\Commands;

use App\Models\Article;
use Illuminate\Console\Command;

class BackfillSlugs extends Command
{
    protected $signature = 'articles:backfill-slugs';
    protected $description = 'Сгенерировать slug для статей, у которых его ещё нет';

    public function handle(): int
    {
        $articles = Article::whereNull('slug')->orWhere('slug', '')->get();

        if ($articles->isEmpty()) {
            $this->info('Все статьи уже имеют slug.');
            return self::SUCCESS;
        }

        foreach ($articles as $article) {
            $article->slug = $article->generateSlug();
            $article->saveQuietly();
            $this->line("#{$article->id} → {$article->slug}");
        }

        $this->info("Готово. Обновлено: {$articles->count()}.");

        return self::SUCCESS;
    }
}
