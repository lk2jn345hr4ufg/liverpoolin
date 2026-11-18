<?php

namespace App\Console\Commands;

use App\Services\TodayMatches;
use Illuminate\Console\Command;

class SyncTodayMatches extends Command
{
    protected $signature = 'matches:today';
    protected $description = 'Обновить матчи дня (АПЛ + Лига чемпионов) для блока «Матчи сегодня»';

    public function handle(TodayMatches $service): int
    {
        try {
            $r = $service->sync();
            $this->info("Готово. Матчей сохранено: {$r['saved']}, старых удалено: {$r['deleted']}.");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }
}
