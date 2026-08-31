<?php

namespace App\Console\Commands;

use App\Services\FootballDataClient;
use Illuminate\Console\Command;

class SyncEuro extends Command
{
    protected $signature = 'fixtures:euro {season : Год начала сезона, например 2025}';
    protected $description = 'Загрузить матчи «Ливерпуля» в европейских турнирах за сезон';

    public function handle(): int
    {
        $season = (int) $this->argument('season');
        $this->info("Загрузка еврокубков за сезон {$season}...");

        try {
            $client = new FootballDataClient();
            $r = $client->syncEuro($season);

            foreach ($r['per'] as $name => $count) {
                $this->line("  {$name}: {$count}");
            }

            $this->info("Готово. Всего матчей загружено: {$r['total']}.");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }
}
