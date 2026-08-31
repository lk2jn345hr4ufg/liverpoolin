<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Fixture;
use App\Models\Standing;
use App\Models\Transfer;
use App\Services\FootballDataClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'scraped'   => Article::where('status', 'scraped')->count(),
            'edited'    => Article::where('status', 'edited')->count(),
            'published' => Article::where('status', 'published')->count(),
            'failed'    => Article::where('status', 'failed')->count(),
        ];

        $recent      = Article::with('category')->latest('scraped_at')->limit(8)->get();
        $nextFixture = Fixture::upcoming()->first();

        $season = Standing::seasons()->first();
        $lfcRow = $season
            ? Standing::tableFor((int) $season)->first(fn ($r) => $r->isLiverpool())
            : null;

        $transferCount = Transfer::count();

        // Годы для выпадающего списка загрузки еврокубков.
        $currentSeason = now()->month >= 7 ? now()->year : now()->year - 1;
        $euroSeasons = range($currentSeason, $currentSeason - 6);

        return view('admin.dashboard', compact(
            'stats', 'recent', 'nextFixture', 'lfcRow', 'season', 'transferCount',
            'euroSeasons', 'currentSeason'
        ));
    }

    public function scrapeNews()
    {
        Artisan::call('scrape:news');

        return back()->with('ok', 'Сбор новостей завершён. ' . trim(Artisan::output()));
    }

    public function editPending()
    {
        Artisan::call('articles:edit-pending', ['--limit' => 20]);

        return back()->with('ok', 'Обработка ИИ завершена. ' . trim(Artisan::output()));
    }

    public function scrapeFixtures()
    {
        Artisan::call('fixtures:sync');

        return back()->with('ok', 'Синхронизация матчей: ' . trim(Artisan::output()));
    }

    public function syncStandings()
    {
        Artisan::call('standings:sync');

        return back()->with('ok', 'Синхронизация таблицы: ' . trim(Artisan::output()));
    }

    public function syncTransfers()
    {
        Artisan::call('transfers:sync');

        return back()->with('ok', 'Синхронизация трансферов: ' . trim(Artisan::output()));
    }

    /** Загрузка еврокубков за выбранный год. */
    public function syncEuro(Request $request)
    {
        $data = $request->validate([
            'season' => 'required|integer|min:2000|max:2100',
        ]);

        try {
            $client = new FootballDataClient();
            $r = $client->syncEuro((int) $data['season']);

            $parts = [];
            foreach ($r['per'] as $name => $count) {
                $parts[] = "{$name}: {$count}";
            }

            return back()->with('ok', sprintf(
                'Еврокубки за сезон %d — %s. Всего загружено: %d.',
                $data['season'],
                implode(', ', $parts),
                $r['total']
            ));
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
