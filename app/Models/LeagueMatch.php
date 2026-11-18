<?php

namespace App\Models;

use Illuminate\Support\Carbon;

/**
 * Матч любого клуба из отслеживаемых турниров (АПЛ, ЛЧ).
 *
 * Наследует Fixture, чтобы переиспользовать логику: русские названия
 * турниров, часовой пояс стадиона, isHome() для подсветки «Ливерпуля».
 */
class LeagueMatch extends Fixture
{
    protected $table = 'league_matches';

    /** Порядок турниров в блоке. */
    public const COMP_ORDER = ['PL' => 0, 'CL' => 1];

    /** Начало и конец «сегодня» в часовом поясе сайта, в UTC. */
    public static function todayRange(): array
    {
        $tz = config('venues.today_timezone', 'Europe/London');

        return [
            Carbon::now($tz)->startOfDay()->utc(),
            Carbon::now($tz)->endOfDay()->utc(),
        ];
    }

    public function scopeToday($q)
    {
        return $q->whereBetween('kickoff_at', self::todayRange());
    }

    public function involvesLiverpool(): bool
    {
        return str_contains(strtolower($this->home_team . ' ' . $this->away_team), 'liverpool');
    }
}
