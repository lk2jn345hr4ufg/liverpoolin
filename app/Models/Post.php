<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Post extends Model
{
    protected $guarded = [];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (Post $post) {
            // Слаг из заголовка (транслитерация кириллицы), уникальный.
            if (blank($post->slug) || $post->isDirty('title')) {
                $post->slug = static::uniqueSlug($post->title, $post->id);
            }

            // Проставить дату публикации при первом переводе в published.
            if ($post->status === 'published' && blank($post->published_at)) {
                $post->published_at = now();
            }
        });
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /* -------- Слаг / ЧПУ -------- */

    public static function uniqueSlug(string $title, $ignoreId = null): string
    {
        $base = Str::slug($title, '-', 'ru') ?: Str::slug($title);
        $base = $base !== '' ? Str::limit($base, 180, '') : 'statya';

        $slug = $base;
        $i = 2;

        while (
            static::where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }

    /** Публичные ссылки строятся по слагу; в админке привязка по id. */
    public function getRouteKey()
    {
        return $this->slug ?: (string) $this->id;
    }

    /* -------- Публикация -------- */

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function publish(): void
    {
        $this->forceFill([
            'status'       => 'published',
            'published_at' => $this->published_at ?: now(),
        ])->save();
    }

    public function unpublish(): void
    {
        $this->forceFill(['status' => 'draft'])->save();
    }

    /* -------- Scopes -------- */

    public function scopePublished($q)
    {
        return $q->where('status', 'published')->orderByDesc('published_at');
    }
}
