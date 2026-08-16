# Расписание матчей через football-data.org

Заменяет ненадёжный HTML-скрапинг liverpoolfc.com на официальный API.

## Почему API, а не скрапинг
liverpoolfc.com формирует расписание на стороне браузера (JavaScript), поэтому
обычный HTTP-запрос возвращает пустую разметку — именно поэтому старый
`scrape:fixtures` находил 0 матчей. API отдаёт чистый JSON, работает стабильно
и не нарушает условия использования.

## Установка

```bash
cd ~/Herd/liverpoolin
unzip -o ~/Downloads/liverpoolin-football-data.zip
php artisan migrate          # добавляет external_id, source, matchday
php artisan route:clear && php artisan view:clear
```

## Получить ключ (бесплатно)
1. Зарегистрируйтесь: https://www.football-data.org/client/register
2. Ключ придёт на почту.
3. Вставьте его в Админке → **Настройки** → «football-data.org».

Ключ хранится в базе в зашифрованном виде (как ключ Gemini).

## Запуск

```bash
php artisan fixtures:sync              # текущий сезон
php artisan fixtures:sync --season=2026 # конкретный сезон
```

Или кнопка **⚽ Обновить расписание** на панели администратора.

Проверить результат:
```bash
php artisan tinker --execute="echo App\Models\Fixture::count();"
```

## Что подтягивается
- Будущие матчи: соперник, дата и время (UTC), турнир, тур, стадион.
- Сыгранные матчи: счёт и статус (`finished`).
- Переносы и отмены: статус `postponed`.

Синхронизация идёт по `external_id`, поэтому повторные запуски **обновляют**
существующие матчи, а не создают дубликаты. Счёт после матча подтянется
автоматически при следующем запуске.

## Автоматизация
В `routes/console.php` уже настроено: синхронизация дважды в день (07:00 и 23:00).
Для локального запуска планировщика:
```bash
php artisan schedule:work
```

## Ограничения бесплатного тарифа
- ~10 запросов в минуту (синхронизация делает 1 запрос — с запасом).
- Доступны основные турниры; матчи турниров вне вашего тарифа просто не придут.
- Ошибка 429 — превышен лимит, подождите минуту. Ошибка 403 — проверьте ключ/тариф.

## Ручное управление сохранено
Матчи по-прежнему можно добавлять и править вручную в Админке → Матчи.
У добавленных вручную `source = manual`, у синхронизированных — `football-data`.
Внимание: при синхронизации данные матчей из API перезаписываются — правьте
вручную только те матчи, которых нет в API.

## Файлы
```
app/Services/FootballDataClient.php                  (новый)
app/Console/Commands/SyncFixtures.php                (новый)
app/Http/Controllers/Admin/SettingController.php     (заменяет)
app/Http/Controllers/Admin/DashboardController.php   (заменяет)
resources/views/admin/settings.blade.php             (заменяет)
resources/views/admin/dashboard.blade.php            (заменяет)
routes/console.php                                   (заменяет)
database/migrations/2026_03_01_000001_...php         (новый)
```

Старый `FixtureScraper` и команда `scrape:fixtures` остаются в проекте, но больше
не используются — можно удалить.
