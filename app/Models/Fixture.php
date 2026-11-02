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

    /** Ключевые слова еврокубков / международных клубных турниров. */
    public const INTERNATIONAL_KEYWORDS = [
        'champions league',
        'europa league',
        'conference league',
        'uefa',
        'super cup',
        'club world cup',
        'intercontinental',
        'friendly',
        'audi cup',
        'premier league summer series',
    ];

    /** Английские турниры — играются по лондонскому времени. */
    public const ENGLISH_KEYWORDS = [
        'premier league',
        'fa cup',
        'efl',
        'league cup',
        'carabao',
        'community shield',
        'championship',
    ];

    /** Русские названия турниров. */
    public const COMPETITION_NAMES = [
        'Premier League'        => 'АПЛ',
        'UEFA Champions League' => 'Лига чемпионов',
        'Champions League'      => 'Лига чемпионов',
        'UEFA Europa League'    => 'Лига Европы',
        'Europa League'         => 'Лига Европы',
        'UEFA Conference League'=> 'Лига конференций',
        'FA Cup'                => 'Кубок Англии',
        'EFL Cup'               => 'Кубок лиги',
        'League Cup'            => 'Кубок лиги',
        'Carabao Cup'           => 'Кубок лиги',
        'Community Shield'      => 'Суперкубок Англии',
        'UEFA Super Cup'        => 'Суперкубок УЕФА',
        'FIFA Club World Cup'   => 'Клубный чемпионат мира',
        'Club Friendlies'       => 'Товарищеские матчи',
    ];

    public static function makeFingerprint(string $home, string $away, ?string $kickoff): string
    {
        return md5(strtolower(trim($home)) . '|' . strtolower(trim($away)) . '|' . ($kickoff ?? ''));
    }

    /* -------- Отображение -------- */

    public function competitionName(): string
    {
        if (blank($this->competition)) {
            return 'Матч';
        }

        return self::COMPETITION_NAMES[$this->competition] ?? $this->competition;
    }

    public function isInternational(): bool
    {
        return $this->competitionMatches(self::INTERNATIONAL_KEYWORDS);
    }

    public function isEnglishCompetition(): bool
    {
        // «Premier League Summer Series» — это турне, не внутренний турнир.
        if ($this->isInternational()) {
            return false;
        }

        return $this->competitionMatches(self::ENGLISH_KEYWORDS);
    }

    private function competitionMatches(array $keywords): bool
    {
        if (blank($this->competition)) {
            return false;
        }

        $c = Str::lower($this->competition);

        foreach ($keywords as $kw) {
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

    public function outcome(): ?string
    {
        if (! $this->hasResult()) {
            return null;
        }

        $our   = $this->isHome() ? $this->home_score : $this->away_score;
        $their = $this->isHome() ? $this->away_score : $this->home_score;

        return $our > $their ? 'win' : ($our === $their ? 'draw' : 'loss');
    }

    /* -------- Часовой пояс стадиона -------- */

    /**
     * Часовой пояс места проведения матча или null, если определить не удалось.
     *
     * 1) ручное значение venue_tz из админки;
     * 2) английский турнир или домашний матч «Ливерпуля» → Лондон;
     * 3) клуб-хозяин из справочника config/venues.php;
     * 4) null — тогда показываем UTC.
     */
    public function venueTimezone(): ?string
    {
        if (filled($this->venue_tz)) {
            return $this->venue_tz;
        }

        if ($this->isEnglishCompetition() || $this->isHome()) {
            return 'Europe/London';
        }

        // Поиск с учётом регистра: «AZ» не должен совпасть с «Lazio».
        foreach (config('venues.teams', []) as $needle => $tz) {
            if (str_contains((string) $this->home_team, $needle)) {
                return $tz;
            }
        }

        return null;
    }

    /** Подпись города для пояса стадиона («Мадрид»), либо «UTC». */
    public function venueCityLabel(): string
    {
        $tz = $this->venueTimezone();

        if ($tz === null) {
            return 'UTC';
        }

        return config("venues.cities.$tz") ?? str_replace('_', ' ', Str::afterLast($tz, '/'));
    }

    /** Время начала по месту проведения (копия, исходная дата не меняется). */
    public function venueKickoff()
    {
        if (! $this->kickoff_at) {
            return null;
        }

        return $this->kickoff_at->copy()->setTimezone($this->venueTimezone() ?? 'UTC');
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
