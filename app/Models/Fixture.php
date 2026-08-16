<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Fixture extends Model
{
    protected $guarded = [];

    protected $casts = [
        'kickoff_at' => 'datetime',
    ];

    /**
     * Ключевые слова еврокубков и международных клубных турниров.
     * Сопоставление идёт по названию турнира из football-data.org,
     * поэтому список — по подстроке и без учёта регистра.
     */
    public const INTERNATIONAL_KEYWORDS = [
        'champions league',
        'europa league',
        'conference league',
        'uefa',
        'super cup',
        'club world cup',
        'intercontinental',
        'friendly',            // товарищеские с зарубежными клубами
        'audi cup',
        'premier league summer series',
    ];

    /** Русские названия турниров для отображения. */
    public const COMPETITION_NAMES = [
        'Premier League'            => 'АПЛ',
        'UEFA Champions League'     => 'Лига чемпионов',
        'Champions League'          => 'Лига чемпионов',
        'UEFA Europa League'        => 'Лига Европы',
        'Europa League'             => 'Лига Европы',
        'FA Cup'                    => 'Кубок Англии',
        'EFL Cup'                   => 'Кубок лиги',
        'League Cup'                => 'Кубок лиги',
        'Carabao Cup'               => 'Кубок лиги',
        'Community Shield'          => 'Суперкубок Англии',
        'UEFA Super Cup'            => 'Суперкубок УЕФА',
        'FIFA Club World Cup'       => 'Клубный чемпионат мира',
        'Club Friendlies'           => 'Товарищеские матчи',
    ];

    public static function makeFingerprint(string $home, string $away, ?string $kickoff): string
    {
        return md5(strtolower(trim($home)) . '|' . strtolower(trim($away)) . '|' . ($kickoff ?? ''));
    }

    /* -------- Отображение -------- */

    /** Название турнира по-русски, если есть в словаре. */
    public function competitionName(): string
    {
        if (blank($this->competition)) {
            return 'Матч';
        }

        return self::COMPETITION_NAMES[$this->competition] ?? $this->competition;
    }

    public function isInternational(): bool
    {
        if (blank($this->competition)) {
            return false;
        }

        $c = Str::lower($this->competition);

        foreach (self::INTERNATIONAL_KEYWORDS as $kw) {
            if (str_contains($c, $kw)) {
                return true;
            }
        }

        return false;
    }

    public function isHome(): bool
    {
        return Str::contains(Str::lower($this->home_team), 'liverpool');
    }

    public function hasResult(): bool
    {
        return $this->home_score !== null && $this->away_score !== null;
    }

    /** 'win' | 'draw' | 'loss' | null — с точки зрения «Ливерпуля». */
    public function outcome(): ?string
    {
        if (! $this->hasResult()) {
            return null;
        }

        $our   = $this->isHome() ? $this->home_score : $this->away_score;
        $their = $this->isHome() ? $this->away_score : $this->home_score;

        return $our > $their ? 'win' : ($our === $their ? 'draw' : 'loss');
    }

    /* -------- Скоупы -------- */

    public function scopeUpcoming($q)
    {
        return $q->where('kickoff_at', '>=', now()->startOfDay())->orderBy('kickoff_at');
    }

    public function scopeResults($q)
    {
        return $q->whereNotNull('home_score')->orderByDesc('kickoff_at');
    }

    /**
     * Фильтр еврокубков средствами SQL (LIKE), чтобы не тянуть всё в память.
     * Совпадает с isInternational(), но выполняется в базе.
     */
    public function scopeInternational($q)
    {
        return $q->where(function ($sub) {
            foreach (self::INTERNATIONAL_KEYWORDS as $kw) {
                $sub->orWhere('competition', 'like', '%' . $kw . '%');
            }
        });
    }

    public function scopeDomestic($q)
    {
        return $q->where(function ($sub) {
            foreach (self::INTERNATIONAL_KEYWORDS as $kw) {
                $sub->where('competition', 'not like', '%' . $kw . '%');
            }
        })->orWhereNull('competition');
    }
}
