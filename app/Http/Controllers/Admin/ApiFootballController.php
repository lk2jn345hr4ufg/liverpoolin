<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\ApiFootballClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class ApiFootballController extends Controller
{
    public function index()
    {
        $status = null;
        $team   = null;
        $error  = null;

        // Запрашиваем статус только если ключ есть — иначе зря тратим квоту.
        if (ApiFootballClient::isConfigured()) {
            try {
                $client = new ApiFootballClient();
                $status = $client->status();
                $team   = $client->teamName();
            } catch (\Throwable $e) {
                $error = $e->getMessage();
            }
        }

        return view('admin.apifootball', [
            'configured' => ApiFootballClient::isConfigured(),
            'teamId'     => ApiFootballClient::teamId(),
            'status'     => $status,
            'teamName'   => $team,
            'error'      => $error,
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'apifootball_key'     => 'nullable|string|max:200',
            'apifootball_team_id' => 'nullable|integer|min:1|max:999999',
        ]);

        if (filled($data['apifootball_key'])) {
            Setting::set('apifootball_key', Crypt::encryptString(trim($data['apifootball_key'])));
        }

        if (filled($data['apifootball_team_id'])) {
            Setting::set('apifootball_team_id', (string) $data['apifootball_team_id']);
        }

        return back()->with('ok', 'Настройки API-Football сохранены.');
    }

    /** Проверка подключения — тратит 2 запроса из суточной квоты. */
    public function test()
    {
        try {
            $client = new ApiFootballClient();
            $status = $client->status();
            $team   = $client->teamName();

            return back()->with('ok', sprintf(
                'Подключение работает. Команда: %s. Использовано запросов: %d из %d.',
                $team ?: '—',
                $status['used'],
                $status['limit']
            ));
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
