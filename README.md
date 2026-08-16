# LiverpoolIn.com

A Laravel + MySQL fan site that **aggregates** Liverpool FC news, **rewrites it with Google Gemini** using an editable prompt, and **publishes only AI-edited content**. Also scrapes the LFC fixtures page.

## Core rule
Raw scraped articles are stored with `status = scraped` and **can never be published**. The `Article::canBePublished()` guard (used by the model and controller) requires `status = edited` with a non-empty AI title + body. So nothing goes live without editing.

## Workflow
```
scrape:news  →  status=scraped (draft, hidden)
                     │
articles:edit-pending (Gemini + your prompt)
                     ▼
                status=edited  →  admin reviews/tweaks  →  Publish  →  status=published (public)
```

## Install (into a fresh Laravel app)
These files are a scaffold. Drop them into a new Laravel 11/12 project:

```bash
composer create-project laravel/laravel liverpoolin
cd liverpoolin
composer require symfony/dom-crawler symfony/css-selector

# copy the app/, database/, resources/, routes/, config/ files from this scaffold over the defaults
# (merge config/services.php into your existing one)

cp .env.example .env
php artisan key:generate
# set DB_* and GEMINI_API_KEY in .env
php artisan migrate
php artisan serve
```

Get a Gemini key at Google AI Studio and put it in `GEMINI_API_KEY`.

## Run the pipeline manually
```bash
php artisan scrape:news              # aggregate drafts
php artisan articles:edit-pending    # AI-edit drafts
php artisan scrape:fixtures          # update schedule
```

## Automate (production)
Add one cron line; Laravel's scheduler (in `routes/console.php`) does the rest:
```
* * * * * cd /path/to/liverpoolin && php artisan schedule:run >> /dev/null 2>&1
```

## Configuring sources
Edit `config/services.php`:
- **`news_sources`** — RSS mode (recommended) or HTML mode with CSS selectors. A "This Is Anfield" RSS feed is enabled as a working default.
- **`fixtures`** — the LFC page URL + selectors.

## ⚠️ Legal notes (read before enabling NewsNow)
- **NewsNow's Terms of Service prohibit scraping/crawling.** The NewsNow HTML source is included but **disabled by default**. Enabling it is at your own legal risk — prefer RSS feeds from the original publishers.
- **Copyright:** AI-rewriting someone else's full article can still infringe. Keep it to genuinely original summaries/angles, always attribute, and link back to the source (the templates already do).
- **Scrape politely:** real user-agent, rate limits, respect `robots.txt`, cache results.
- The fixtures page and NewsNow are JS-heavy. If plain HTTP returns empty markup, you'll need a headless browser (e.g. `spatie/browsershot` + Puppeteer, or Playwright) — or an official data feed/API.

## Add auth before launch
The `/admin` routes are open in this scaffold. Add `composer require laravel/breeze`, install it, and put `->middleware('auth')` on the admin route group in `routes/web.php`.

## File map
```
app/Models/{Article,Fixture,Setting}.php
app/Services/{NewsScraper,FixtureScraper,GeminiEditor}.php
app/Console/Commands/{ScrapeNews,ScrapeFixtures,EditPendingArticles}.php
app/Http/Controllers/PublicController.php
app/Http/Controllers/Admin/{ArticleController,SettingController}.php
database/migrations/*  routes/{web,console}.php  config/services.php
resources/views/{layouts,public,admin}/*
```
# liverpoolin
