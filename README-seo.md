# SEO-friendly URL для новостей + карта сайта

Было: `/news/2`. Стало: `/news/2-kvansah-audiciya-dlya-vozvrasheniya`.
Число в начале — надёжный якорь, дальше транслитерированный заголовок.

## Установка
```bash
cd ~/Herd/liverpoolin
unzip -o ~/Downloads/liverpoolin-seo-urls.zip
php artisan migrate
php artisan articles:backfill-slugs      # заполнить slug у существующих статей
php artisan route:clear && php artisan view:clear
```

## Как это работает
- Поле `slug` заполняется автоматически при сохранении статьи (хук `saving`)
  и обновляется, если поменяли заголовок.
- `Str::slug($title, '-', 'ru')` транслитерирует кириллицу: «Квансах» → «kvansah».
- Резолвинг маршрута идёт по числовому id из начала слага — устойчиво к любым
  изменениям хвоста.
- Старые ссылки `/news/2` и неверные слаги делают **301-редирект** на канонический
  URL. Это правильно для SEO: вес страницы не теряется, дубли не плодятся.
- В админке статьи резолвятся по `:id` (в маршрутах), поэтому панель работает
  как раньше.

## Что ещё добавлено для SEO
- **`/sitemap.xml`** — карта сайта: главная, разделы, категории и все статьи.
- **`/robots.txt`** — с указанием на sitemap и запретом индексации `/admin`, `/login`.
- **Мета-теги** на каждой странице: `description`, canonical, Open Graph
  (превью в соцсетях/мессенджерах), Twitter Card.
- **Schema.org NewsArticle** (JSON-LD) на страницах статей — помогает Google
  показывать их как новости.

## После деплоя
Добавьте карту сайта в Google Search Console и Яндекс.Вебмастер:
`https://ваш-домен/sitemap.xml`

## Файлы
```
database/migrations/2026_06_01_000001_add_slug_to_articles.php   (новый)
app/Models/Article.php                                           (заменяет)
app/Http/Controllers/PublicController.php                        (заменяет)
app/Http/Controllers/SitemapController.php                       (новый)
app/Console/Commands/BackfillSlugs.php                           (новый)
resources/views/sitemap.blade.php                                (новый)
resources/views/public/partials/head.blade.php                   (заменяет)
resources/views/public/article.blade.php                         (заменяет)
routes/web.php                                                    (заменяет)
```
