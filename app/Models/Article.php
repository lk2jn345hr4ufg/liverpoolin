<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Article extends Model
{
    protected $guarded = [];

    protected $casts = [
        'scraped_at'   => 'datetime',
        'edited_at'    => 'datetime',
        'published_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        // Держим slug в актуальном состоянии: заполняем при создании и
        // обновляем, когда меняется отредактированный заголовок.
        static::saving(function (Article $article) {
            if (blank($article->slug) || $article->isDirty('edited_title')) {
                $article->slug = $article->generateSlug();
            }
        });
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /* -------- Slug / ЧПУ -------- */

    /**
     * URL вида "2-kvansah-audiciya" — число впереди как надёжный якорь,
     * дальше транслитерированный заголовок для читаемости и SEO.
     */
    public function generateSlug(): string
    {
        $title = $this->edited_title ?: $this->original_title ?: 'article';

        // Str::slug транслитерирует кириллицу в латиницу.
        $body = Str::slug($title, '-', 'ru');

        if ($body === '') {
            $body = Str::slug($title); // запасной вариант
        }

        // Ограничиваем длину, не разрывая слово посередине.
        $body = Str::limit($body, 70, '');
        $body = trim($body, '-');

        $id = $this->id ?: (static::max('id') + 1);

        return $body === '' ? (string) $id : "{$id}-{$body}";
    }

    /** Laravel строит ссылки route('article.show', $article) по этому ключу. */
    public function getRouteKey()
    {
        return $this->slug ?: (string) $this->id;
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    /** Числовой id в начале слага — для резолвинга маршрута. */
    public static function idFromSlug(string $value): ?int
    {
        return preg_match('/^(\d+)/', $value, $m) ? (int) $m[1] : null;
    }

    /* -------- Публикация -------- */

    public function canBePublished(): bool
    {
        return $this->status === 'edited'
            && filled($this->edited_title)
            && filled($this->edited_content);
    }

    public function publish(): bool
    {
        if (! $this->canBePublished()) {
            return false;
        }

        $this->forceFill(['status' => 'published', 'published_at' => now()])->save();

        return true;
    }

    public function unpublish(): void
    {
        $this->forceFill(['status' => 'edited', 'published_at' => null])->save();
    }

    public function displayTitle(): string
    {
        return $this->edited_title ?: '[unedited]';
    }

    /* -------- Scopes -------- */

    public function scopePublished($q)
    {
        return $q->where('status', 'published')->orderByDesc('published_at');
    }

    public function scopePendingEdit($q)
    {
        return $q->where('status', 'scraped');
    }
}
