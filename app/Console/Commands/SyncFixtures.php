<?php

namespace App\Console\Commands;

use App\Services\FootballDataClient;
use Illuminate\Console\Command;

class SyncFixtures extends Command
{
    protected $signature = 'fixtures:sync {--season= : Год начала сезона, например 2026}';
    protected $description = 'Синхронизировать матчи и результаты «Ливерпуля» с football-data.org';

    public function handle(): int
    {
        $this->info('Синхронизация с football-data.org...');

        try {
            $client = new FootballDataClient();
            $season = $this->option('season') ? (int) $this->option('season') : null;

            $r = $client->sync($season);

            $this->info("Готово. Получено: {$r['synced']}, добавлено: {$r['created']}, обновлено: {$r['updated']}.");

            if ($r['synced'] === 0) {
                $this->warn('Матчи не найдены. Проверьте ключ, сезон и тариф на football-data.org.');
            }

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }
}
