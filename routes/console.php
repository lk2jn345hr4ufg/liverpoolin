<?php

use Illuminate\Support\Facades\Schedule;

/*
 | Планировщик Laravel 11+.
 | На сервере достаточно одной cron-строки:
 |   * * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
 | Локально: php artisan schedule:work
 */

// Новости — каждый час.
Schedule::command('scrape:news')->hourly()->withoutOverlapping();

// Обработка ИИ — каждые 15 минут.
Schedule::command('articles:edit-pending --limit=15')
    ->everyFifteenMinutes()
    ->withoutOverlapping();

// Матчи «Ливерпуля» — дважды в день.
Schedule::command('fixtures:sync')->twiceDaily(7, 23);

// Таблица — раз в день.
Schedule::command('standings:sync')->dailyAt('23:30');

// Трансферы — раз в день (у API-Football 100 запросов в сутки).
Schedule::command('transfers:sync')->dailyAt('05:00');

// Матчи дня (АПЛ + ЛЧ) — каждые 10 минут, чтобы счёт и статус были свежими.
// Один запрос; лимит football-data.org — 10 запросов в минуту.
Schedule::command('matches:today')->everyTenMinutes()->withoutOverlapping();
