<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\Transfer;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Клиент API-Football (api-sports.io).
 *
 * football-data.org НЕ отдаёт трансферы — поэтому нужен этот источник.
 * Матчи и таблицу продолжаем брать из football-data.org, чтобы не жечь
 * суточную квоту (100 запросов на бесплатном тарифе).
 *
 * ID «Ливерпуля» здесь — 40 (в football-data.org — 64, это разные системы).
 */
class ApiFootballClient
{
    public const BASE = 'https://v3.football.api-sports.io';
    public const LIVERPOOL_TEAM_ID = 40;
    public const PL_LEAGUE_ID = 39;

    private string $apiKey;

    public function __construct(?string $apiKey = null)
    {
        $this->apiKey = $apiKey
            ?: $this->readKey(Setting::get('apifootball_key'))
            ?: (string) config('services.apifootball.key');

        if (empty($this->apiKey)) {
            throw new RuntimeException(
                'Ключ API-Football не задан. Добавьте его на странице «API-Football».'
            );
        }
    }

    public static function isConfigured(): bool
    {
        return filled(Setting::get('apifootball_key'))
            || filled(config('services.apifootball.key'));
    }

    public static function teamId(): int
    {
        return (int) (Setting::get('apifootball_team_id') ?: self::LIVERPOOL_TEAM_ID);
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

    public function get(string $path, array $query = []): array
    {
        $response = Http::timeout(30)
            ->retry(2, 1500, throw: false)
            ->withHeaders(['x-apisports-key' => $this->apiKey])
            ->get(self::BASE . '/' . ltrim($path, '/'), $query);

        if ($response->status() === 429) {
            throw new RuntimeException('Исчерпан суточный лимит запросов API-Football (100/сутки на бесплатном тарифе).');
        }

        if ($response->status() === 499 || $response->status() === 401) {
            throw new RuntimeException('API-Football: неверный ключ.');
        }

        if ($response->failed()) {
            Log::error('API-Football error', ['path' => $path, 'body' => $response->body()]);

            throw new RuntimeException('Ошибка запроса к API-Football: ' . $response->status());
        }

        $json = $response->json() ?? [];

        // API-Football отвечает 200 даже при ошибке — она лежит в errors.
        $errors = data_get($json, 'errors');

        if (! empty($errors) && is_array($errors)) {
            throw new RuntimeException('API-Football: ' . implode('; ', array_map('strval', $errors)));
        }

        return $json;
    }

    /** @return array{account:?string, plan:?string, end:?string, used:int, limit:int, left:int} */
    public function status(): array
    {
        $json = $this->get('status');
        $r = data_get($json, 'response', []);

        $used  = (int) data_get($r, 'requests.current', 0);
        $limit = (int) data_get($r, 'requests.limit_day', 0);

        return [
            'account' => data_get($r, 'account.email'),
            'plan'    => data_get($r, 'subscription.plan'),
            'end'     => data_get($r, 'subscription.end'),
            'used'    => $used,
            'limit'   => $limit,
            'left'    => max(0, $limit - $used),
        ];
    }

    public function teamName(?int $teamId = null): ?string
    {
        $json = $this->get('teams', ['id' => $teamId ?: self::teamId()]);

        return data_get($json, 'response.0.team.name');
    }

    /* ==================== ТРАНСФЕРЫ ==================== */

    /**
     * Синхронизировать трансферы команды. Один запрос к API.
     *
     * Важно: /transfers?team=X возвращает ВСЮ карьеру игроков, связанных
     * с командой, включая переходы между другими клубами. Поэтому ниже
     * оставляем только те записи, где наша команда — одна из сторон.
     *
     * @return array{fetched:int, saved:int, created:int}
     */
    public function syncTransfers(?int $teamId = null): array
    {
        $teamId = $teamId ?: self::teamId();

        $json = $this->get('transfers', ['team' => $teamId]);
        $players = data_get($json, 'response', []);

        $saved = 0;
        $created = 0;

        foreach ($players as $entry) {
            $playerId   = (int) data_get($entry, 'player.id');
            $playerName = (string) data_get($entry, 'player.name');

            if (blank($playerName)) {
                continue;
            }

            foreach (data_get($entry, 'transfers', []) as $t) {
                $inId  = (int) data_get($t, 'teams.in.id');
                $outId = (int) data_get($t, 'teams.out.id');

                // Отсекаем переходы, не касающиеся нашего клуба.
                if ($inId !== $teamId && $outId !== $teamId) {
                    continue;
                }

                $direction = $inId === $teamId ? 'in' : 'out';
                $date      = data_get($t, 'date');
                $typeRaw   = data_get($t, 'type');

                $clubFrom = data_get($t, 'teams.out.name');
                $clubTo   = data_get($t, 'teams.in.name');

                $record = Transfer::updateOrCreate(
                    ['external_key' => Transfer::makeKey($playerId, $playerName, $date, $clubFrom, $clubTo)],
                    [
                        'player_id'      => $playerId ?: null,
                        'player_name'    => $playerName,
                        'transfer_date'  => $date,
                        'season'         => Transfer::seasonFromDate($date),
                        'direction'      => $direction,
                        'club_from'      => $clubFrom,
                        'club_from_logo' => data_get($t, 'teams.out.logo'),
                        'club_to'        => $clubTo,
                        'club_to_logo'   => data_get($t, 'teams.in.logo'),
                        'type_raw'       => $typeRaw,
                        'kind'           => Transfer::normaliseKind($typeRaw),
                        'fee_text'       => $typeRaw,
                    ]
                );

                $saved++;
                $record->wasRecentlyCreated && $created++;
            }
        }

        return ['fetched' => count($players), 'saved' => $saved, 'created' => $created];
    }
}
