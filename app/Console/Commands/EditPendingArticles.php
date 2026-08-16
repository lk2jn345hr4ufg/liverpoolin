<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Services\GeminiEditor;
use Illuminate\Console\Command;

class EditPendingArticles extends Command
{
    protected $signature = 'articles:edit-pending {--limit=10}';
    protected $description = 'Run scraped drafts through Gemini AI (rewrite + category) using the admin prompt.';

    public function handle(GeminiEditor $editor): int
    {
        $articles = Article::pendingEdit()->limit((int) $this->option('limit'))->get();

        if ($articles->isEmpty()) {
            $this->info('No pending articles to edit.');
            return self::SUCCESS;
        }

        foreach ($articles as $article) {
            $this->line("Editing #{$article->id}: {$article->original_title}");
            $article->update(['status' => 'editing']);

            try {
                $result = $editor->edit($article->original_title, $article->original_content ?? '');

                $article->update([
                    'edited_title'   => $result['title'],
                    'edited_content' => $result['content'],
                    'category_id'    => $result['category_id'],
                    'status'         => 'edited',
                    'edited_at'      => now(),
                    'edit_error'     => null,
                ]);

                $cat = $article->fresh()->category?->name ?? 'без категории';
                $this->info("  -> edited [{$cat}]");
            } catch (\Throwable $e) {
                $article->update(['status' => 'failed', 'edit_error' => $e->getMessage()]);
                $this->error("  -> failed: {$e->getMessage()}");
            }
        }

        return self::SUCCESS;
    }
}
