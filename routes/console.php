<?php

use Illuminate\Support\Facades\Schedule;

/*
 | Планировщик Laravel 11/12.
 | Продакшен — одна cron-строка:
 |   * * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
 | Локально: php artisan schedule:work
 */

// Новости — каждый час.
Schedule::command('scrape:news')->hourly()->withoutOverlapping();

// Обработка ИИ — каждые 15 минут.
Schedule::command('articles:edit-pending --limit=15')
    ->everyFifteenMinutes()
    ->withoutOverlapping();

// Матчи — дважды в день (football-data.org).
Schedule::command('fixtures:sync')->twiceDaily(7, 23);

// Таблица — раз в день; снимки накапливают историю по турам.
Schedule::command('standings:sync')->dailyAt('23:30');

// Трансферы — раз в день. У API-Football всего 100 запросов в сутки
// на бесплатном тарифе, а синхронизация тратит 1, так что чаще не нужно.
Schedule::command('transfers:sync')->dailyAt('05:00');
