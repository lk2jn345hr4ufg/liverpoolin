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
 * Клиент football-data.org: матчи, таблица, еврокубки.
 *
 * ID «Ливерпуля» — 64.
 *
 * Про еврокубки: на бесплатном тарифе стабильно доступна только
 * Лига чемпионов (CL). Лига Европы (EL) и Лига конференций (EC/UECL)
 * обычно возвращают 403 — это не ошибка кода, а ограничение тарифа.
 */
class FootballDataClient
{
    public const LIVERPOOL_TEAM_ID = 64;
    public const PL_CODE = 'PL';
    public const ERR_FORBIDDEN = 'FORBIDDEN';

    /** Европейские клубные турниры: код => отображаемое имя. */
    public const EURO_COMPETITIONS = [
        'CL'   => 'Лига чемпионов',
        'EL'   => 'Лига Европы',
        'EC'   => 'Лига конференций',
    ];

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
            ->retry(2, 2000, throw: false)
            ->withHeaders(['X-Auth-Token' => $this->apiKey])
            ->get($url, $query);

        if ($response->status() === 429) {
            throw new RuntimeException('Превышен лимит запросов football-data.org (~10/мин). Подождите минуту.');
        }

        if ($response->status() === 403) {
            throw new RuntimeException(self::ERR_FORBIDDEN . ': нет доступа к этому турниру/сезону на текущем тарифе.');
        }

        if ($response->status() === 400) {
            throw new RuntimeException('Некорректный запрос — проверьте год сезона.');
        }

        if ($response->failed()) {
            Log::error('football-data.org error', ['url' => $url, 'body' => $response->body()]);

            throw new RuntimeException('Ошибка запроса к football-data.org: ' . $response->status());
        }

        return $response;
    }

    public function get(string $path, array $query = []): array
    {
        return $this->request('https://api.football-data.org/v4/' . ltrim($path, '/'), $query)->json();
    }

    /* ==================== МАТЧИ КОМАНДЫ ==================== */

    /** @return array{synced:int, created:int, updated:int} */
    public function sync(?int $season = null): array
    {
        $teamId = (int) (Setting::get('football_data_team_id') ?: self::LIVERPOOL_TEAM_ID);

        $matches = data_get(
            $this->request("https://api.football-data.org/v4/teams/{$teamId}/matches", $season ? ['season' => $season] : [])->json(),
            'matches',
            []
        );

        $created = 0;
        $updated = 0;

        foreach ($matches as $m) {
            $this->store($m) ? $created++ : $updated++;
        }

        return ['synced' => count($matches), 'created' => $created, 'updated' => $updated];
    }

    /* ==================== ЕВРОКУБКИ ==================== */

    /**
     * Загрузить матчи «Ливерпуля» из всех европейских турниров за сезон.
     * Недоступные турниры (403) пропускаются, а не роняют процесс.
     *
     * @return array{season:int, per:array<string,int|string>, total:int}
     */
    public function syncEuro(int $season, int $teamId = null): array
    {
        $teamId = $teamId ?: (int) (Setting::get('football_data_team_id') ?: self::LIVERPOOL_TEAM_ID);

        $per = [];
        $total = 0;

        foreach (self::EURO_COMPETITIONS as $code => $name) {
            try {
                $json = $this->request(
                    "https://api.football-data.org/v4/competitions/{$code}/matches",
                    ['season' => $season]
                )->json();

                $ours = collect(data_get($json, 'matches', []))
                    ->filter(fn ($m) => (int) data_get($m, 'homeTeam.id') === $teamId
                                     || (int) data_get($m, 'awayTeam.id') === $teamId);

                foreach ($ours as $m) {
                    $this->store($m);
                }

                $per[$name] = $ours->count();
                $total += $ours->count();
            } catch (\Throwable $e) {
                // 403 = турнир недоступен на тарифе; помечаем и идём дальше.
                $per[$name] = str_contains($e->getMessage(), self::ERR_FORBIDDEN)
                    ? 'нет доступа'
                    : 'ошибка';
            }
        }

        return ['season' => $season, 'per' => $per, 'total' => $total];
    }

    private function store(array $m): bool
    {
        $home = data_get($m, 'homeTeam.shortName') ?: data_get($m, 'homeTeam.name');
        $away = data_get($m, 'awayTeam.shortName') ?: data_get($m, 'awayTeam.name');

        if (! $home || ! $away) {
            return false;
        }

        $kickoff = data_get($m, 'utcDate') ? Carbon::parse($m['utcDate']) : null;

        $fixture = Fixture::updateOrCreate(
            ['external_id' => data_get($m, 'id')],
            [
                'source'      => 'football-data',
                'competition' => data_get($m, 'competition.name'),
                'matchday'    => is_numeric(data_get($m, 'matchday')) ? data_get($m, 'matchday') : null,
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

        return $fixture->wasRecentlyCreated;
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

    /** @return array{season:int, matchday:int, rows:int} */
    public function syncStandings(?int $season = null, string $code = self::PL_CODE): array
    {
        $query = $season ? ['season' => $season] : [];

        $response = $this->request("https://api.football-data.org/v4/competitions/{$code}/standings", $query);
        $json = $response->json();

        $season   = $this->resolveSeason($json);
        $matchday = (int) (data_get($json, 'season.currentMatchday') ?: 0);

        $table = collect(data_get($json, 'standings', []))->firstWhere('type', 'TOTAL');
        $rows  = data_get($table, 'table', []);

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
