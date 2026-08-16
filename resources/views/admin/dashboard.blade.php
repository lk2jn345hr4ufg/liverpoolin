@extends('layouts.admin')
@section('title', 'Панель')

@section('content')
    <h1>Панель</h1>

    <div class="grid" style="margin-bottom:16px">
        <div class="stat"><div class="n">{{ $stats['scraped'] }}</div><div class="l">Черновики</div></div>
        <div class="stat"><div class="n">{{ $stats['edited'] }}</div><div class="l">Обработаны</div></div>
        <div class="stat"><div class="n">{{ $stats['published'] }}</div><div class="l">Опубликованы</div></div>
        <div class="stat"><div class="n">{{ $stats['failed'] }}</div><div class="l">Ошибки</div></div>
    </div>

    <div class="card">
        <h3>Быстрые действия</h3>
        <p class="muted">Запуск вручную. Сбор новостей может занять несколько секунд.</p>
        <form class="inline" method="post" action="{{ route('admin.run.scrapeNews') }}">@csrf
            <button class="btn primary">⬇ Собрать новости</button>
        </form>
        <form class="inline" method="post" action="{{ route('admin.run.editPending') }}">@csrf
            <button class="btn primary">✨ Обработать ИИ</button>
        </form>
        <form class="inline" method="post" action="{{ route('admin.run.scrapeFixtures') }}">@csrf
            <button class="btn ghost">⚽ Расписание</button>
        </form>
        <form class="inline" method="post" action="{{ route('admin.run.syncStandings') }}">@csrf
            <button class="btn ghost">📊 Таблица</button>
        </form>
        <form class="inline" method="post" action="{{ route('admin.run.syncTransfers') }}">@csrf
            <button class="btn ghost">🔁 Трансферы</button>
        </form>
    </div>

    @if($lfcRow)
        <div class="card">
            <h3>«Ливерпуль» в таблице — сезон {{ $season }}</h3>
            <strong>{{ $lfcRow->position }}-е место</strong>
            <span class="muted">
                · {{ $lfcRow->points }} очков · {{ $lfcRow->played }} матчей ·
                {{ $lfcRow->won }}–{{ $lfcRow->draw }}–{{ $lfcRow->lost }} ·
                разница {{ $lfcRow->goal_difference > 0 ? '+' : '' }}{{ $lfcRow->goal_difference }}
            </span>
        </div>
    @endif

    @if($nextFixture)
        <div class="card">
            <h3>Ближайший матч</h3>
            <strong>{{ $nextFixture->home_team }}</strong> — <strong>{{ $nextFixture->away_team }}</strong>
            <span class="muted">
                · {{ $nextFixture->kickoff_at?->isoFormat('D MMMM YYYY, HH:mm') ?? 'дата уточняется' }}
                @if($nextFixture->competition) · {{ $nextFixture->competitionName() }} @endif
            </span>
        </div>
    @endif

    <div class="card">
        <h3>Трансферы</h3>
        <p class="muted">
            Записей в базе: <strong>{{ $transferCount }}</strong>.
            Источник — API-Football. <a href="{{ route('transfers') }}" target="_blank">Открыть страницу на сайте ↗</a>
        </p>
    </div>

    <div class="card">
        <h3>Последние статьи</h3>
        <table>
            <thead><tr><th>Заголовок</th><th>Категория</th><th>Статус</th><th></th></tr></thead>
            <tbody>
            @forelse($recent as $a)
                <tr>
                    <td>{{ \Illuminate\Support\Str::limit($a->displayTitle() ?: $a->original_title, 50) }}</td>
                    <td>
                        @if($a->category)
                            <span class="badge" style="background:{{ $a->category->color }};color:#fff">{{ $a->category->name }}</span>
                        @else
                            <span class="muted">—</span>
                        @endif
                    </td>
                    <td><span class="badge {{ $a->status }}">{{ $a->status }}</span></td>
                    <td><a class="btn ghost" href="{{ route('admin.articles.edit', $a) }}">Открыть</a></td>
                </tr>
            @empty
                <tr><td colspan="4" class="muted">Пока пусто — нажмите «Собрать новости».</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
