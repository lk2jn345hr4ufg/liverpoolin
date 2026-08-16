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

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * CORE RULE: content may only go live once it has been AI-edited.
     * A raw scraped article can never be published.
     */
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

        $this->forceFill([
            'status'       => 'published',
            'published_at' => now(),
        ])->save();

        return true;
    }

    public function unpublish(): void
    {
        $this->forceFill([
            'status'       => 'edited',
            'published_at' => null,
        ])->save();
    }

    /** Title shown publicly — always the edited version. */
    public function displayTitle(): string
    {
        return $this->edited_title ?: '[unedited]';
    }

    public function slug(): string
    {
        return Str::slug($this->displayTitle()) . '-' . $this->id;
    }

    /* -------- Query scopes -------- */

    public function scopePublished($q)
    {
        return $q->where('status', 'published')->orderByDesc('published_at');
    }

    public function scopePendingEdit($q)
    {
        return $q->where('status', 'scraped');
    }

    public function scopeInCategory($q, $categoryId)
    {
        return $q->where('category_id', $categoryId);
    }
}
