<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
{
    protected $guarded = [];

    public function articles()
    {
        return $this->hasMany(Article::class);
    }

    public function publishedArticles()
    {
        return $this->articles()->where('status', 'published');
    }

    /** Ordered list used in menus, admin dropdowns and the AI prompt. */
    public function scopeOrdered($q)
    {
        return $q->orderBy('position')->orderBy('name');
    }

    public static function makeSlug(string $name): string
    {
        // Str::slug transliterates Cyrillic, so "Трансферы" -> "transfery".
        $slug = Str::slug($name);

        return $slug !== '' ? $slug : 'category-' . uniqid();
    }

    /**
     * Compact list the AI sees, e.g.
     *   transfers: Трансферные слухи, подписания...
     */
    public static function promptList(): string
    {
        return static::ordered()->get()
            ->map(fn ($c) => "- {$c->slug}: {$c->name}. {$c->description}")
            ->implode("\n");
    }
}
