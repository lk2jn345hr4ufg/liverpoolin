# Категории + автоматическая классификация ИИ

Gemini выбирает категорию в том же запросе, где переписывает статью — дополнительных
вызовов API (и расходов) нет.

## Как это работает
1. `Category::promptList()` собирает список категорий с описаниями.
2. `GeminiEditor` добавляет его к вашему промпту и просит вернуть
   `{"title","content","category"}`.
3. Возвращённый slug проверяется по таблице `categories`. Неизвестное значение → `null`
   (статья просто останется без категории, ошибки не будет).
4. В админке категорию можно поменять вручную — выбор ИИ не окончательный.

## Установка
```bash
cd ~/Herd/liverpoolin
unzip -o ~/Downloads/liverpoolin-categories.zip
php artisan migrate          # создаёт categories + category_id у articles
```

Затем откройте `ROUTES-add-categories.php` и добавьте маршруты в `routes/web.php`
(инструкция внутри файла), после чего:

```bash
rm ROUTES-add-categories.php
php artisan route:clear && php artisan view:clear
```

## Категории по умолчанию
Трансферы, Матчи, Игроки, Травмы, Клуб, Аналитика.
Меняются в админке: **/admin/categories**

Поле «Описание для ИИ» — самое важное: именно по нему Gemini решает,
куда отнести статью. Чем конкретнее, тем точнее классификация.

## Классификация уже существующих статей
Статьи, обработанные до установки, категории не имеют. Прогоните их заново:
```bash
php artisan tinker --execute="App\Models\Article::whereIn('status',['published','edited'])->update(['status'=>'scraped','published_at'=>null]); echo 'reset';"
php artisan articles:edit-pending
```

## Что появилось на сайте
- Панель категорий под шапкой на всех публичных страницах.
- Цветные метки категорий на карточках и в главном материале.
- Страница категории: `/category/transfers`, `/category/matches` и т.д.
- В админке: фильтр статей по категории + выпадающий список для ручной правки.

## Файлы
```
database/migrations/2026_02_01_000001_create_categories_table.php   (новый)
app/Models/Category.php                                             (новый)
app/Models/Article.php                                              (заменяет)
app/Services/GeminiEditor.php                                       (заменяет)
app/Console/Commands/EditPendingArticles.php                        (заменяет)
app/Http/Controllers/PublicController.php                           (заменяет)
app/Http/Controllers/Admin/ArticleController.php                    (заменяет)
app/Http/Controllers/Admin/CategoryController.php                   (новый)
resources/views/public/partials/{head,header,footer}.blade.php       (новые)
resources/views/public/{home,article,fixtures,category}.blade.php    (заменяет/новый)
resources/views/admin/{articles,article_edit,categories}.blade.php   (заменяет/новый)
```
