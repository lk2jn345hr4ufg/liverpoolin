<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use App\Models\Fixture;
use App\Models\Standing;
use App\Models\Transfer;

class PublicController extends Controller
{
    public function home()
    {
        $latest   = Article::published()->with('category')->limit(13)->get();
        $featured = $latest->first();
        $articles = $latest->skip(1)->values();

        $fixtures    = Fixture::upcoming()->limit(5)->get();
        $nextFixture = $fixtures->first();

        $categories = Category::ordered()->get();

        return view('public.home', compact(
            'featured', 'articles', 'fixtures', 'nextFixture', 'categories'
        ));
    }

    public function category(string $slug)
    {
        $category = Category::where('slug', $slug)->firstOrFail();

        $articles = Article::published()
            ->with('category')
            ->where('category_id', $category->id)
            ->paginate(12);

        $categories = Category::ordered()->get();

        return view('public.category', compact('category', 'articles', 'categories'));
    }

    /**
     * Показ статьи по ЧПУ-слагу.
     *
     * Резолвим по числовому id из начала слага — это устойчиво к любым
     * расхождениям в хвосте (напр. если заголовок отредактировали).
     * Если пришёл «неканоничный» slug, делаем 301-редирект на правильный,
     * что заодно закрывает старые ссылки вида /news/2.
     */
    public function show(string $slug)
    {
        $id = Article::idFromSlug($slug);

        abort_if($id === null, 404);

        $article = Article::findOrFail($id);

        abort_unless($article->status === 'published', 404);

        // Каноничный URL — редиректим, если пришли по устаревшему/битому слагу.
        if ($slug !== $article->getRouteKey()) {
            return redirect()->route('article.show', $article, 301);
        }

        $article->load('category');
        $categories = Category::ordered()->get();

        return view('public.article', compact('article', 'categories'));
    }

    public function fixtures()
    {
        $comp = request('comp');

        $all = Fixture::query()
            ->when($comp, fn ($q) => $q->where('competition', $comp))
            ->orderBy('kickoff_at')
            ->get();

        return view('public.fixtures', [
            'grouped'      => $this->groupByMonth($all),
            'competitions' => $this->competitionOptions(),
            'activeComp'   => $comp,
            'nextFixture'  => Fixture::upcoming()->first(),
            'stats'        => $this->summarise($all),
            'categories'   => Category::ordered()->get(),
            'title'        => 'Расписание',
            'intro'        => 'Все матчи «Ливерпуля» в сезоне — прошедшие и предстоящие.',
            'isIntl'       => false,
        ]);
    }

    public function internationalFixtures()
    {
        $all = Fixture::international()->orderBy('kickoff_at')->get();

        return view('public.fixtures', [
            'grouped'      => $this->groupByMonth($all),
            'competitions' => $all->pluck('competition')->filter()->unique()->values(),
            'activeComp'   => null,
            'nextFixture'  => $all->first(fn ($f) => $f->kickoff_at && $f->kickoff_at->isFuture()),
            'stats'        => $this->summarise($all),
            'categories'   => Category::ordered()->get(),
            'title'        => 'Международные матчи',
            'intro'        => 'Еврокубки и международные турниры: Лига чемпионов, Лига Европы, Суперкубок УЕФА, клубный чемпионат мира.',
            'isIntl'       => true,
        ]);
    }

    public function table()
    {
        $seasons = Standing::seasons();

        if ($seasons->isEmpty()) {
            return view('public.table', [
                'rows' => collect(), 'seasons' => $seasons, 'season' => null,
                'matchday' => 0, 'history' => [], 'lfc' => null,
                'categories' => Category::ordered()->get(),
            ]);
        }

        $season = (int) (request('season') ?: $seasons->first());

        if (! $seasons->contains($season)) {
            $season = (int) $seasons->first();
        }

        $rows = Standing::tableFor($season);

        return view('public.table', [
            'rows'       => $rows,
            'seasons'    => $seasons,
            'season'     => $season,
            'matchday'   => Standing::latestMatchday($season),
            'history'    => Standing::historyFor($season, Standing::LIVERPOOL_TEAM_ID),
            'lfc'        => $rows->first(fn ($r) => $r->isLiverpool()),
            'categories' => Category::ordered()->get(),
        ]);
    }

    public function transfers()
    {
        $seasons = Transfer::seasons();
        $season  = request('season');
        $dir     = request('dir');

        if (filled($season) && ! $seasons->contains((int) $season)) {
            $season = null;
        }

        $query = Transfer::query()
            ->when(filled($season), fn ($q) => $q->where('season', (int) $season))
            ->when(in_array($dir, ['in', 'out'], true), fn ($q) => $q->where('direction', $dir))
            ->orderByDesc('transfer_date');

        $transfers = (clone $query)->get();

        $base = Transfer::query()->when(filled($season), fn ($q) => $q->where('season', (int) $season));

        return view('public.transfers', [
            'transfers'  => $transfers,
            'grouped'    => $transfers->groupBy(fn ($t) => $t->transfer_date
                ? $t->transfer_date->isoFormat('MMMM YYYY')
                : 'Дата неизвестна'),
            'seasons'    => $seasons,
            'season'     => $season ? (int) $season : null,
            'dir'        => in_array($dir, ['in', 'out'], true) ? $dir : null,
            'countIn'    => (clone $base)->where('direction', 'in')->count(),
            'countOut'   => (clone $base)->where('direction', 'out')->count(),
            'categories' => Category::ordered()->get(),
        ]);
    }

    /* -------- helpers -------- */

    private function groupByMonth($fixtures)
    {
        return $fixtures->groupBy(function ($f) {
            return $f->kickoff_at ? $f->kickoff_at->isoFormat('MMMM YYYY') : 'Дата уточняется';
        });
    }

    private function competitionOptions()
    {
        return Fixture::query()
            ->whereNotNull('competition')
            ->distinct()
            ->orderBy('competition')
            ->pluck('competition');
    }

    private function summarise($fixtures): array
    {
        $played = $fixtures->filter(fn ($f) => $f->hasResult());

        return [
            'total'    => $fixtures->count(),
            'played'   => $played->count(),
            'upcoming' => $fixtures->count() - $played->count(),
            'win'      => $played->filter(fn ($f) => $f->outcome() === 'win')->count(),
            'draw'     => $played->filter(fn ($f) => $f->outcome() === 'draw')->count(),
            'loss'     => $played->filter(fn ($f) => $f->outcome() === 'loss')->count(),
        ];
    }
}
