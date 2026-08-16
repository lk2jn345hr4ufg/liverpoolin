<?php

namespace App\Services;

use App\Models\Fixture;
use App\Models\Setting;
use App\Models\Standing;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Клиент football-data.org: матчи «Ливерпуля» и турнирная таблица АПЛ.
 *
 * Бесплатный тариф: ~10 запросов в минуту. Каждая синхронизация — 1 запрос.
 * Регистрация ключа: https://www.football-data.org/client/register
 */
class FootballDataClient
{
    public const LIVERPOOL_TEAM_ID = 64;
    public const PL_CODE = 'PL';

    private string $apiKey;

    public function __construct(?string $apiKey = null)
    {
        $this->apiKey = $apiKey
            ?: $this->readKey(Setting::get('football_data_key'))
            ?: (string) config('services.football_data.key');

        if (empty($this->apiKey)) {
            throw new RuntimeException(
                'Ключ football-data.org не задан. Добавьте его в Админке → Настройки.'
            );
        }
    }

    private function readKey(?string $stored): ?string
    {
        if (blank($stored)) {
            return null;
        }

        try {
            return Crypt::decryptString($stored);
        } catch (DecryptException $e) {
            return $stored;
        }
    }

    private function request(string $url, array $query = [])
    {
        $response = Http::timeout(30)
            ->retry(2, 2000)
            ->withHeaders(['X-Auth-Token' => $this->apiKey])
            ->get($url, $query);

        if ($response->status() === 429) {
            throw new RuntimeException('Превышен лимит запросов football-data.org. Подождите минуту.');
        }

        if ($response->status() === 403) {
            throw new RuntimeException('football-data.org: доступ запрещён — проверьте ключ или тариф.');
        }

        if ($response->failed()) {
            Log::error('football-data.org error', ['url' => $url, 'body' => $response->body()]);
            throw new RuntimeException('Ошибка запроса к football-data.org: ' . $response->status());
        }

        return $response;
    }

    /* ==================== МАТЧИ ==================== */

    /** @return array{synced:int, created:int, updated:int} */
    public function sync(?int $season = null): array
    {
        $teamId = (int) (Setting::get('football_data_team_id') ?: self::LIVERPOOL_TEAM_ID);

        $query = $season ? ['season' => $season] : [];

        $response = $this->request("https://api.football-data.org/v4/teams/{$teamId}/matches", $query);

        $matches = data_get($response->json(), 'matches', []);
        $created = 0;
        $updated = 0;

        foreach ($matches as $m) {
            $home = data_get($m, 'homeTeam.shortName') ?: data_get($m, 'homeTeam.name');
            $away = data_get($m, 'awayTeam.shortName') ?: data_get($m, 'awayTeam.name');

            if (! $home || ! $away) {
                continue;
            }

            $kickoff = data_get($m, 'utcDate') ? Carbon::parse($m['utcDate']) : null;

            $fixture = Fixture::updateOrCreate(
                ['external_id' => data_get($m, 'id')],
                [
                    'source'      => 'football-data',
                    'competition' => data_get($m, 'competition.name'),
                    'matchday'    => data_get($m, 'matchday'),
                    'home_team'   => $home,
                    'away_team'   => $away,
                    'venue'       => data_get($m, 'venue'),
                    'kickoff_at'  => $kickoff,
                    'home_score'  => data_get($m, 'score.fullTime.home'),
                    'away_score'  => data_get($m, 'score.fullTime.away'),
                    'status'      => $this->mapStatus(data_get($m, 'status')),
                    'fingerprint' => Fixture::makeFingerprint($home, $away, $kickoff?->toIso8601String()),
                ]
            );

            $fixture->wasRecentlyCreated ? $created++ : $updated++;
        }

        return ['synced' => count($matches), 'created' => $created, 'updated' => $updated];
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

    /* ==================== ТАБЛИЦА ==================== */

    /**
     * Синхронизировать турнирную таблицу.
     *
     * Снимок сохраняется с текущим туром, поэтому регулярные запуски
     * постепенно накапливают историю движения команд по турам.
     *
     * @return array{season:int, matchday:int, rows:int}
     */
    public function syncStandings(?int $season = null, string $code = self::PL_CODE): array
    {
        $query = $season ? ['season' => $season] : [];

        $response = $this->request("https://api.football-data.org/v4/competitions/{$code}/standings", $query);
        $json = $response->json();

        $season   = $this->resolveSeason($json);
        $matchday = (int) (data_get($json, 'season.currentMatchday') ?: 0);

        // Берём общую таблицу (TOTAL), а не домашнюю/гостевую.
        $table = collect(data_get($json, 'standings', []))->firstWhere('type', 'TOTAL');

        $rows = data_get($table, 'table', []);

        if (empty($rows)) {
            return ['season' => $season, 'matchday' => $matchday, 'rows' => 0];
        }

        foreach ($rows as $r) {
            Standing::updateOrCreate(
                [
                    'competition_code' => $code,
                    'season'           => $season,
                    'matchday'         => $matchday,
                    'team_id'          => (int) data_get($r, 'team.id'),
                ],
                [
                    'team_name'       => data_get($r, 'team.name'),
                    'team_short'      => data_get($r, 'team.shortName') ?: data_get($r, 'team.tla'),
                    'team_crest'      => data_get($r, 'team.crest'),
                    'position'        => (int) data_get($r, 'position'),
                    'played'          => (int) data_get($r, 'playedGames'),
                    'won'             => (int) data_get($r, 'won'),
                    'draw'            => (int) data_get($r, 'draw'),
                    'lost'            => (int) data_get($r, 'lost'),
                    'points'          => (int) data_get($r, 'points'),
                    'goals_for'       => (int) data_get($r, 'goalsFor'),
                    'goals_against'   => (int) data_get($r, 'goalsAgainst'),
                    'goal_difference' => (int) data_get($r, 'goalDifference'),
                    'form'            => data_get($r, 'form'),
                ]
            );
        }

        return ['season' => $season, 'matchday' => $matchday, 'rows' => count($rows)];
    }

    /**
     * Определить сезон из ответа API.
     * Порядок: filters.season -> год из season.startDate -> текущий год.
     */
    private function resolveSeason(array $json): int
    {
        $fromFilters = data_get($json, 'filters.season');

        if (! blank($fromFilters)) {
            return (int) $fromFilters;
        }

        $startDate = data_get($json, 'season.startDate');

        if (! blank($startDate)) {
            return (int) substr((string) $startDate, 0, 4);
        }

        return (int) now()->year;
    }
}
