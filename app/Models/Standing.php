<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Standing extends Model
{
    protected $guarded = [];

    public const LIVERPOOL_TEAM_ID = 64;

    /** Коды турниров → русские названия. */
    public const COMP_NAMES = [
        'PL' => 'АПЛ',
        'CL' => 'Лига чемпионов',
        'EL' => 'Лига Европы',
        'EC' => 'Лига конференций',
    ];

    public static function compName(string $code): string
    {
        return self::COMP_NAMES[$code] ?? $code;
    }

    /** Зоны таблицы — для подсветки (актуально для АПЛ). */
    public function zone(): ?string
    {
        return match (true) {
            $this->position <= 4  => 'ucl',
            $this->position === 5 => 'uel',
            $this->position >= 18 => 'relegation',
            default               => null,
        };
    }

    public function isLiverpool(): bool
    {
        return $this->team_id === self::LIVERPOOL_TEAM_ID
            || Str::contains(Str::lower($this->team_name), 'liverpool');
    }

    public function formArray(): array
    {
        if (blank($this->form)) {
            return [];
        }

        return array_slice(
            array_filter(array_map('trim', explode(',', $this->form))),
            -5
        );
    }

    /* -------- Запросы -------- */

    /** Коды турниров, по которым есть сохранённые таблицы. */
    public static function competitions()
    {
        return static::query()
            ->distinct()
            ->orderByRaw("CASE competition_code WHEN 'PL' THEN 0 ELSE 1 END, competition_code")
            ->pluck('competition_code');
    }

    public static function seasons(string $code = 'PL')
    {
        return static::where('competition_code', $code)
            ->distinct()
            ->orderByDesc('season')
            ->pluck('season');
    }

    public static function latestMatchday(int $season, string $code = 'PL'): int
    {
        return (int) static::where('competition_code', $code)
            ->where('season', $season)
            ->max('matchday');
    }

    public static function tableFor(int $season, string $code = 'PL')
    {
        $md = static::latestMatchday($season, $code);

        return static::where('competition_code', $code)
            ->where('season', $season)
            ->where('matchday', $md)
            ->orderBy('position')
            ->get();
    }

    public static function historyFor(int $season, int $teamId, string $code = 'PL'): array
    {
        return static::where('competition_code', $code)
            ->where('season', $season)
            ->where('team_id', $teamId)
            ->orderBy('matchday')
            ->get(['matchday', 'position', 'points'])
            ->map(fn ($r) => [
                'matchday' => (int) $r->matchday,
                'position' => (int) $r->position,
                'points'   => (int) $r->points,
            ])
            ->all();
    }
}
