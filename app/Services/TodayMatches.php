<?php

namespace App\Services;

use App\Models\LeagueMatch;
use App\Models\Setting;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Загружает матчи всего тура АПЛ и Лиги чемпионов на вчера–завтра
 * (запас на разницу часовых поясов) одним запросом к football-data.org.
 */
class TodayMatches
{
    public const COMPETITIONS = 'PL,CL';

    public function sync(): array
    {
        $key = $this->apiKey();

        $from = Carbon::now('UTC')->subDay()->toDateString();
        $to   = Carbon::now('UTC')->addDay()->toDateString();

        $response = Http::timeout(20)
            ->retry(2, 2000, throw: false)
            ->withHeaders(['X-Auth-Token' => $key])
            ->get('https://api.football-data.org/v4/matches', [
                'competitions' => self::COMPETITIONS,
                'dateFrom'     => $from,
                'dateTo'       => $to,
            ]);

        if ($response->status() === 429) {
            throw new RuntimeException('Превышен лимит запросов football-data.org. Подождите минуту.');
        }

        if ($response->failed()) {
            Log::error('football-data.org /matches error', ['body' => $response->body()]);
            throw new RuntimeException('Ошибка запроса к football-data.org: ' . $response->status());
        }

        $saved = 0;

        foreach (data_get($response->json(), 'matches', []) as $m) {
            $home = data_get($m, 'homeTeam.shortName') ?: data_get($m, 'homeTeam.name');
            $away = data_get($m, 'awayTeam.shortName') ?: data_get($m, 'awayTeam.name');

            if (! $home || ! $away) {
                continue;
            }

            LeagueMatch::updateOrCreate(
                ['external_id' => data_get($m, 'id')],
                [
                    'competition_code' => data_get($m, 'competition.code'),
                    'competition'      => data_get($m, 'competition.name'),
                    'matchday'         => is_numeric(data_get($m, 'matchday')) ? data_get($m, 'matchday') : null,
                    'home_team'        => $home,
                    'away_team'        => $away,
                    'home_crest'       => data_get($m, 'homeTeam.crest'),
                    'away_crest'       => data_get($m, 'awayTeam.crest'),
                    'venue'            => data_get($m, 'venue'),
                    'kickoff_at'       => data_get($m, 'utcDate') ? Carbon::parse($m['utcDate'])->utc() : null,
                    'home_score'       => data_get($m, 'score.fullTime.home'),
                    'away_score'       => data_get($m, 'score.fullTime.away'),
                    'status'           => $this->mapStatus(data_get($m, 'status')),
                ]
            );

            $saved++;
        }

        // Старые записи не нужны — держим таблицу маленькой.
        $deleted = LeagueMatch::where('kickoff_at', '<', now()->subDays(3))->delete();

        return ['saved' => $saved, 'deleted' => $deleted];
    }

    private function mapStatus(?string $status): string
    {
        return match ($status) {
            'IN_PLAY', 'PAUSED'                   => 'live',
            'FINISHED', 'AWARDED'                 => 'finished',
            'POSTPONED', 'SUSPENDED', 'CANCELLED' => 'postponed',
            default                               => 'scheduled',
        };
    }

    private function apiKey(): string
    {
        $stored = Setting::get('football_data_key');

        if (filled($stored)) {
            try {
                return Crypt::decryptString($stored);
            } catch (DecryptException $e) {
                return $stored;
            }
        }

        $key = (string) config('services.football_data.key');

        if ($key === '') {
            throw new RuntimeException('Ключ football-data.org не задан. Добавьте его в Админке → Настройки.');
        }

        return $key;
    }
}
