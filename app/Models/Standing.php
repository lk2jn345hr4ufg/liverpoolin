<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Standing extends Model
{
    protected $guarded = [];

    public const LIVERPOOL_TEAM_ID = 64;

    /** Зоны АПЛ — для подсветки строк. */
    public function zone(): ?string
    {
        return match (true) {
            $this->position <= 4  => 'ucl',      // Лига чемпионов
            $this->position === 5 => 'uel',      // Лига Европы
            $this->position >= 18 => 'relegation',
            default               => null,
        };
    }

    public function isLiverpool(): bool
    {
        return $this->team_id === self::LIVERPOOL_TEAM_ID
            || Str::contains(Str::lower($this->team_name), 'liverpool');
    }

    /** Форма в виде массива: ['W','D','L',...] */
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

    /** Список сезонов, по которым есть данные (свежие первыми). */
    public static function seasons(string $code = 'PL')
    {
        return static::where('competition_code', $code)
            ->distinct()
            ->orderByDesc('season')
            ->pluck('season');
    }

    /** Максимальный сохранённый тур для сезона. */
    public static function latestMatchday(int $season, string $code = 'PL'): int
    {
        return (int) static::where('competition_code', $code)
            ->where('season', $season)
            ->max('matchday');
    }

    /** Таблица сезона на последний известный тур. */
    public static function tableFor(int $season, string $code = 'PL')
    {
        $md = static::latestMatchday($season, $code);

        return static::where('competition_code', $code)
            ->where('season', $season)
            ->where('matchday', $md)
            ->orderBy('position')
            ->get();
    }

    /**
     * История позиции команды по турам сезона.
     * @return array<int, array{matchday:int, position:int, points:int}>
     */
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
