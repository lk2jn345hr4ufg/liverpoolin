# LiverpoolIn — Admin panel update

Adds a proper admin panel on top of the existing scaffold:

- **Dashboard** (`/admin`) — status counts, next fixture, recent articles, and one-click
  buttons to scrape news, run the AI editor, and scrape fixtures.
- **Fixtures management** — list upcoming + past, add fixtures manually, edit scores/status, delete.
- **Articles** — re-skinned list with scrape/AI-edit/delete actions.
- **Settings** — same encrypted-key + model + prompt form, in the new layout.
- A shared **sidebar layout** (`layouts/admin`) used by every admin page.

## What's in this zip
```
app/Http/Controllers/Admin/DashboardController.php   (new)
app/Http/Controllers/Admin/FixtureController.php     (new)
resources/views/layouts/admin.blade.php              (new)
resources/views/admin/dashboard.blade.php            (new)
resources/views/admin/fixtures.blade.php             (new)
resources/views/admin/fixture_edit.blade.php         (new)
resources/views/admin/articles.blade.php             (overwrites existing)
resources/views/admin/article_edit.blade.php         (overwrites existing)
resources/views/admin/settings.blade.php             (overwrites existing)
ROUTES-add-to-web.php                                 (reference — copy into routes/web.php)
```

## Install
```bash
cd ~/Herd/liverpoolin
unzip -o ~/Downloads/liverpoolin-admin-panel.zip
```

Then open `ROUTES-add-to-web.php`, and paste its route group + imports into your
`routes/web.php`, replacing your current `admin` route group. (It's kept as a
separate file so the unzip doesn't clobber any Breeze auth routes you've added.)

Delete the reference file afterwards if you like:
```bash
rm ROUTES-add-to-web.php
```

Clear caches and visit the panel:
```bash
php artisan route:clear
php artisan view:clear
```
Open https://liverpoolin.test/admin

## Auth
The route group uses `->middleware('auth')`. If you've installed Laravel Breeze,
you'll be redirected to log in. If you haven't installed Breeze yet, remove
`->middleware('auth')` from the group for now (see notes in ROUTES-add-to-web.php).
