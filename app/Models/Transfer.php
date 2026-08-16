<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class Transfer extends Model
{
    protected $guarded = [];

    protected $casts = [
        'transfer_date' => 'date',
    ];

    public const KINDS = [
        'fee'     => 'Выкуп',
        'loan'    => 'Аренда',
        'free'    => 'Свободный агент',
        'unknown' => 'Не указано',
    ];

    public static function makeKey(?int $playerId, string $name, ?string $date, ?string $from, ?string $to): string
    {
        return md5(implode('|', [
            $playerId ?: Str::lower($name),
            $date ?: '',
            Str::lower((string) $from),
            Str::lower((string) $to),
        ]));
    }

    /**
     * Сезон по дате трансфера. Футбольный сезон идёт с июля по июнь,
     * поэтому январский трансфер относится к сезону предыдущего года.
     */
    public static function seasonFromDate(?string $date): int
    {
        if (blank($date)) {
            return (int) now()->year;
        }

        $d = Carbon::parse($date);

        return $d->month >= 7 ? $d->year : $d->year - 1;
    }

    /** Нормализовать поле type из API в один из KINDS. */
    public static function normaliseKind(?string $type): string
    {
        $t = Str::lower(trim((string) $type));

        return match (true) {
            $t === '' || $t === 'n/a' => 'unknown',
            str_contains($t, 'free')  => 'free',
            str_contains($t, 'loan')  => 'loan',
            default                   => 'fee',   // '€ 40M' и подобное
        };
    }

    public function kindLabel(): string
    {
        return self::KINDS[$this->kind] ?? 'Не указано';
    }

    /** Сумма для отображения — только когда это реальный выкуп. */
    public function feeLabel(): ?string
    {
        return $this->kind === 'fee' ? $this->fee_text : null;
    }

    public function isArrival(): bool
    {
        return $this->direction === 'in';
    }

    /* -------- Скоупы -------- */

    public function scopeArrivals($q)
    {
        return $q->where('direction', 'in');
    }

    public function scopeDepartures($q)
    {
        return $q->where('direction', 'out');
    }

    public function scopeInSeason($q, int $season)
    {
        return $q->where('season', $season);
    }

    public static function seasons()
    {
        return static::distinct()->orderByDesc('season')->pluck('season');
    }
}
