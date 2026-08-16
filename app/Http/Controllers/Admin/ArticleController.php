<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use App\Services\GeminiEditor;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        $status     = $request->get('status', 'all');
        $categoryId = $request->get('category');

        $articles = Article::query()
            ->with('category')
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->when($categoryId, fn ($q) => $q->where('category_id', $categoryId))
            ->latest('scraped_at')
            ->paginate(20)
            ->withQueryString();

        $categories = Category::ordered()->get();

        return view('admin.articles', compact('articles', 'status', 'categories', 'categoryId'));
    }

    public function edit(Article $article)
    {
        $categories = Category::ordered()->get();

        return view('admin.article_edit', compact('article', 'categories'));
    }

    /** Manually (re)run this article through Gemini. */
    public function aiEdit(Article $article, GeminiEditor $editor)
    {
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

            return back()->with('ok', 'Статья переписана ИИ.');
        } catch (\Throwable $e) {
            $article->update(['status' => 'failed', 'edit_error' => $e->getMessage()]);

            return back()->with('error', 'Ошибка Gemini: ' . $e->getMessage());
        }
    }

    /** Human tweaks — including overriding the AI's category choice. */
    public function update(Request $request, Article $article)
    {
        $data = $request->validate([
            'edited_title'   => 'required|string|max:500',
            'edited_content' => 'required|string',
            'category_id'    => 'nullable|exists:categories,id',
        ]);

        $article->update($data + ['status' => 'edited', 'edited_at' => now()]);

        return back()->with('ok', 'Сохранено.');
    }

    public function publish(Article $article)
    {
        if (! $article->publish()) {
            return back()->with('error', 'Статью нужно сначала отредактировать через ИИ.');
        }

        return back()->with('ok', 'Опубликовано.');
    }

    public function unpublish(Article $article)
    {
        $article->unpublish();

        return back()->with('ok', 'Снято с публикации.');
    }

    public function destroy(Article $article)
    {
        $article->delete();

        return back()->with('ok', 'Удалено.');
    }
}
