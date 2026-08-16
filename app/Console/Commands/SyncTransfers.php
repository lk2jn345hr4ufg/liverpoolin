<?php

namespace App\Console\Commands;

use App\Services\ApiFootballClient;
use Illuminate\Console\Command;

class SyncTransfers extends Command
{
    protected $signature = 'transfers:sync {--team= : ID команды в API-Football (по умолчанию — из настроек)}';
    protected $description = 'Синхронизировать трансферы «Ливерпуля» с API-Football';

    public function handle(): int
    {
        $this->info('Синхронизация трансферов с API-Football...');

        try {
            $client = new ApiFootballClient();
            $team   = $this->option('team') ? (int) $this->option('team') : null;

            $r = $client->syncTransfers($team);

            $this->info("Готово. Игроков в ответе: {$r['fetched']}, записей сохранено: {$r['saved']}, новых: {$r['created']}.");

            if ($r['saved'] === 0) {
                $this->warn('Трансферы не найдены. Проверьте ID команды и доступные сезоны на вашем тарифе.');
            }

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }
}
