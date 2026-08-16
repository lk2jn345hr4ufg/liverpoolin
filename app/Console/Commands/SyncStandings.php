<?php

namespace App\Console\Commands;

use App\Services\FootballDataClient;
use Illuminate\Console\Command;

class SyncStandings extends Command
{
    protected $signature = 'standings:sync
                            {--season= : Год старта сезона, напр. 2025}
                            {--from= : Загрузить диапазон сезонов, напр. 2020}
                            {--to= : Конец диапазона, напр. 2026}';

    protected $description = 'Синхронизировать турнирную таблицу АПЛ с football-data.org';

    public function handle(): int
    {
        try {
            $client = new FootballDataClient();

            // Диапазон сезонов — для наполнения архива.
            if ($this->option('from')) {
                $from = (int) $this->option('from');
                $to   = (int) ($this->option('to') ?: now()->year);

                for ($s = $from; $s <= $to; $s++) {
                    $this->line("Сезон {$s}...");

                    try {
                        $r = $client->syncStandings($s);
                        $this->info("  сохранено строк: {$r['rows']} (тур {$r['matchday']})");
                    } catch (\Throwable $e) {
                        $this->warn('  пропущен: ' . $e->getMessage());
                    }

                    // Бережём лимит бесплатного тарифа (~10 запросов в минуту).
                    sleep(7);
                }

                return self::SUCCESS;
            }

            $season = $this->option('season') ? (int) $this->option('season') : null;
            $r = $client->syncStandings($season);

            $this->info("Готово. Сезон {$r['season']}, тур {$r['matchday']}, строк: {$r['rows']}.");

            if ($r['rows'] === 0) {
                $this->warn('Таблица пустая. Проверьте ключ, сезон и тариф на football-data.org.');
            }

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }
}
