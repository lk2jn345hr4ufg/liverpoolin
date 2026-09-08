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

    public function show(string $slug)
    {
        $id = Article::idFromSlug($slug);

        abort_if($id === null, 404);

        $article = Article::findOrFail($id);

        abort_unless($article->status === 'published', 404);

        if ($slug !== $article->getRouteKey()) {
            return redirect()->route('article.show', $article, 301);
        }

        $article->load('category');
        $categories = Category::ordered()->get();

        return view('public.article', compact('article', 'categories'));
    }

    /**
     * Расписание: самое актуальное сверху.
     * Порядок: сначала предстоящие матчи (ближайший — первым),
     * затем сыгранные по убыванию (самый свежий результат — выше).
     */
    public function fixtures()
    {
        $comp = request('comp');

        $all = Fixture::query()
            ->when($comp, fn ($q) => $q->where('competition', $comp))
            ->get();

        $ordered = $this->actualFirst($all);

        return view('public.fixtures', [
            'grouped'      => $this->groupByMonth($ordered),
            'competitions' => $this->competitionOptions(),
            'activeComp'   => $comp,
            'nextFixture'  => Fixture::upcoming()->first(),
            'stats'        => $this->summarise($ordered),
            'categories'   => Category::ordered()->get(),
            'title'        => 'Расписание',
            'intro'        => 'Матчи «Ливерпуля»: ближайшие сверху, сыгранные ниже.',
            'isIntl'       => false,
            'seasons'      => collect(),
            'activeSeason' => null,
            'intlComps'    => collect(),
        ]);
    }

    public function internationalFixtures()
    {
        $base = Fixture::international();

        $everything = (clone $base)->get();

        $seasons = $everything
            ->map(fn ($f) => $this->seasonOf($f))
            ->filter()
            ->unique()
            ->sortDesc()
            ->values();

        $activeSeason = request('season');
        $activeSeason = ($activeSeason !== null && $seasons->contains((int) $activeSeason))
            ? (int) $activeSeason
            : $seasons->first();

        $activeComp = request('comp');

        $filtered = $everything
            ->when($activeSeason !== null, fn ($c) => $c->filter(fn ($f) => $this->seasonOf($f) === $activeSeason))
            ->when($activeComp, fn ($c) => $c->filter(fn ($f) => $f->competition === $activeComp))
            ->values();

        $intlComps = $everything
            ->when($activeSeason !== null, fn ($c) => $c->filter(fn ($f) => $this->seasonOf($f) === $activeSeason))
            ->pluck('competition')
            ->filter()
            ->unique()
            ->values();

        $nextIntl = $everything
            ->filter(fn ($f) => $f->kickoff_at && $f->kickoff_at->isFuture())
            ->sortBy('kickoff_at')
            ->first();

        return view('public.fixtures', [
            'grouped'      => $this->groupByMonth($this->actualFirst($filtered)),
            'competitions' => collect(),
            'activeComp'   => $activeComp,
            'nextFixture'  => $nextIntl,
            'stats'        => $this->summarise($filtered),
            'categories'   => Category::ordered()->get(),
            'title'        => 'Международные матчи',
            'intro'        => 'Еврокубки «Ливерпуля»: Лига чемпионов, Лига Европы, Лига конференций и Суперкубок УЕФА.',
            'isIntl'       => true,
            'seasons'      => $seasons,
            'activeSeason' => $activeSeason,
            'intlComps'    => $intlComps,
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

        $baseT = Transfer::query()->when(filled($season), fn ($q) => $q->where('season', (int) $season));

        return view('public.transfers', [
            'transfers'  => $transfers,
            'grouped'    => $transfers->groupBy(fn ($t) => $t->transfer_date
                ? $t->transfer_date->isoFormat('MMMM YYYY')
                : 'Дата неизвестна'),
            'seasons'    => $seasons,
            'season'     => $season ? (int) $season : null,
            'dir'        => in_array($dir, ['in', 'out'], true) ? $dir : null,
            'countIn'    => (clone $baseT)->where('direction', 'in')->count(),
            'countOut'   => (clone $baseT)->where('direction', 'out')->count(),
            'categories' => Category::ordered()->get(),
        ]);
    }

    /* -------- helpers -------- */

    /**
     * «Актуальное сверху»: будущие матчи по возрастанию (ближайший первым),
     * затем прошедшие по убыванию (свежий результат первым).
     * Матчи без даты — в самый конец.
     */
    private function actualFirst($fixtures)
    {
        $now = now();

        $future = $fixtures
            ->filter(fn ($f) => $f->kickoff_at && $f->kickoff_at->gte($now))
            ->sortBy('kickoff_at')
            ->values();

        $past = $fixtures
            ->filter(fn ($f) => $f->kickoff_at && $f->kickoff_at->lt($now))
            ->sortByDesc('kickoff_at')
            ->values();

        $noDate = $fixtures->filter(fn ($f) => ! $f->kickoff_at)->values();

        return $future->concat($past)->concat($noDate);
    }

    private function seasonOf(Fixture $f): ?int
    {
        if (! $f->kickoff_at) {
            return null;
        }

        return $f->kickoff_at->month >= 7
            ? $f->kickoff_at->year
            : $f->kickoff_at->year - 1;
    }

    /**
     * Группировка по месяцу с сохранением порядка коллекции.
     * groupBy у Collection не пересортировывает элементы, поэтому
     * порядок из actualFirst() сохраняется: будущие месяцы, затем прошлые.
     */
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
