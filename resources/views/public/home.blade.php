<!DOCTYPE html>
<html lang="ru">
<head>
    @include('public.partials.head', ['title' => 'LiverpoolIn — новости «Ливерпуля»'])
    <style>
        .board{background:var(--wine);color:#fff;background-image:linear-gradient(90deg,var(--wine-2),var(--wine))}
        .board .in{max-width:1120px;margin:0 auto;padding:8px 20px;display:flex;align-items:center;gap:18px;min-height:56px;flex-wrap:wrap}
        .board .ey{font-weight:800;font-size:11px;letter-spacing:2px;color:var(--gold);text-transform:uppercase;border-right:1px solid rgba(255,255,255,.25);padding-right:16px}
        .board .match{font-family:var(--display);font-size:20px;text-transform:uppercase;letter-spacing:.5px}
        .board .vs{color:var(--gold);margin:0 8px}
        .board .meta{font-size:13px;color:rgba(255,255,255,.8);font-weight:600}
        .board .more{margin-left:auto;font-weight:800;font-size:12px;letter-spacing:1px;text-transform:uppercase;color:var(--gold);border:1px solid var(--gold);padding:7px 12px;border-radius:2px}
        .board .more:hover{background:var(--gold);color:var(--wine)}

        .hero{margin:26px 0}
        .splash{position:relative;border-radius:6px;overflow:hidden;min-height:430px;display:flex;align-items:flex-end;background:var(--wine)}
        .splash .ph{position:absolute;inset:0;background:radial-gradient(120% 120% at 80% 0%, rgba(197,16,46,.55), transparent 60%),linear-gradient(180deg,var(--wine),var(--wine-2))}
        .splash img{position:absolute;inset:0}
        .splash .scrim{position:absolute;inset:0;background:linear-gradient(180deg,rgba(20,10,12,.05) 30%,rgba(20,10,12,.86) 100%)}
        .splash .in{position:relative;padding:32px;color:#fff;max-width:760px}
        .splash h1{font-family:var(--display);font-weight:400;text-transform:uppercase;line-height:.98;letter-spacing:.5px;font-size:clamp(28px,5vw,54px);margin:12px 0 10px;text-wrap:balance}
        .splash p{font-size:16px;color:rgba(255,255,255,.85);max-width:620px}
        .splash .by{margin-top:14px;font-size:12px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:var(--gold)}

        /* ---------- Дашборд ---------- */
        .dash{display:grid;grid-template-columns:repeat(auto-fit,minmax(255px,1fr));gap:18px;margin:10px 0 30px}
        .panel{background:var(--card);border:1px solid var(--line);border-radius:8px;overflow:hidden;display:flex;flex-direction:column}
        .panel .ph{display:flex;align-items:center;gap:10px;padding:13px 16px;border-bottom:2px solid var(--red)}
        .panel .ph h2{font-family:var(--display);font-weight:400;text-transform:uppercase;font-size:16px;letter-spacing:1px}
        .panel .ph a{margin-left:auto;font-size:11px;font-weight:800;letter-spacing:.8px;text-transform:uppercase;color:var(--red)}
        .panel .bd{padding:6px 0;flex:1}

        .fxrow{display:flex;align-items:center;gap:10px;padding:9px 16px;border-bottom:1px solid var(--line)}
        .fxrow:last-child{border-bottom:0}
        .fxrow .dt{flex:0 0 46px;text-align:center;line-height:1.1}
        .fxrow .dt .d{font-family:var(--display);font-size:16px}
        .fxrow .dt .m{font-size:9px;font-weight:800;text-transform:uppercase;color:var(--muted);letter-spacing:.5px}
        .fxrow .tm{font-size:13px;font-weight:700;line-height:1.3;min-width:0}
        .fxrow .tm .us{color:var(--red)}
        .fxrow .sc{margin-left:auto;font-family:var(--display);font-size:16px;letter-spacing:1px;padding:1px 7px;border-radius:3px;flex:0 0 auto}
        .fxrow .sc.w{background:#1a7f4b;color:#fff} .fxrow .sc.d{background:#b8901f;color:#fff} .fxrow .sc.l{background:var(--red);color:#fff}
        .fxrow .tt{margin-left:auto;flex:0 0 auto;text-align:right}
        .fxrow .tt .ko{align-items:flex-end}
        .board .ko-venue{color:#fff} .board .ko-venue small{color:rgba(255,255,255,.7)} .board .ko-user{color:var(--gold)}
        .fxrow .cp{font-size:9px;font-weight:800;text-transform:uppercase;color:var(--muted);letter-spacing:.5px}
        .panel .none{padding:18px 16px;color:var(--muted);font-size:13px}

        table.mini{width:100%;border-collapse:collapse}
        table.mini th{font-size:9px;font-weight:800;text-transform:uppercase;color:var(--muted);letter-spacing:.5px;text-align:center;padding:7px 4px;border-bottom:1px solid var(--line)}
        table.mini th.tm{text-align:left;padding-left:14px}
        table.mini td{font-size:12.5px;text-align:center;padding:6px 4px;border-bottom:1px solid var(--line);font-variant-numeric:tabular-nums}
        table.mini td.tm{text-align:left;padding-left:14px;font-weight:700}
        table.mini tr:last-child td{border-bottom:0}
        table.mini tr.lfc{background:rgba(200,16,46,.07)} table.mini tr.lfc td.tm{color:var(--red)}
        table.mini .pos{font-family:var(--display);width:26px}
        table.mini .zone{display:inline-block;width:3px;height:16px;border-radius:2px;vertical-align:middle;margin-right:6px;background:transparent}
        table.mini .zone.ucl{background:#1a56b8} table.mini .zone.uel{background:#f5b22d} table.mini .zone.rel{background:var(--red)}
        table.mini .crest{width:15px;height:15px;object-fit:contain;vertical-align:middle;margin-right:6px;display:inline-block}
        table.mini .pts{font-weight:800}
        .panel .scroll{max-height:360px;overflow:auto}

        .cols{display:grid;grid-template-columns:1fr 320px;gap:34px;padding-bottom:20px}
        .side h2{font-family:var(--display);font-weight:400;text-transform:uppercase;font-size:18px;letter-spacing:1px;padding-bottom:12px;border-bottom:3px solid var(--red);margin-bottom:14px}
        .fx{display:flex;flex-direction:column;gap:10px}
        .fx .item{background:var(--card);border:1px solid var(--line);border-left:4px solid var(--gold);border-radius:4px;padding:12px 13px}
        .fx .comp{font-size:10px;font-weight:800;letter-spacing:1.5px;text-transform:uppercase;color:var(--muted)}
        .fx .teams{font-family:var(--display);font-size:16px;text-transform:uppercase;margin:3px 0 2px}
        .fx .when{font-size:12px;color:var(--muted);font-weight:600}
        .side .allfx{display:inline-block;margin-top:14px;font-weight:800;font-size:12px;letter-spacing:1px;text-transform:uppercase;color:var(--red)}

        @media(max-width:820px){.cols{grid-template-columns:1fr;gap:26px}}
    </style>
</head>
<body>

@include('public.partials.header', ['activeCat' => null])

<div class="board">
    <div class="in">
        <span class="ey">Следующий матч</span>
        @if($nextFixture)
            <span class="match">{{ $nextFixture->home_team }}<span class="vs">—</span>{{ $nextFixture->away_team }}</span>
            <span class="meta">
                {{ $nextFixture->venueKickoff()?->isoFormat('dd, D MMM') ?? 'Дата уточняется' }} ·
                @include('public.partials.kickoff', ['f' => $nextFixture, 'compact' => true])
                @if($nextFixture->competition) — {{ $nextFixture->competitionName() }} @endif
            </span>
        @else
            <span class="match">Матч уточняется</span>
        @endif
        <a class="more" href="{{ route('fixtures') }}">Все матчи</a>
    </div>
</div>

<main class="wrap">
    <section class="hero">
        @if($featured)
            <a class="splash" href="{{ route('article.show', $featured) }}">
                @if($featured->image_url)<div class="ph"></div><img src="{{ $featured->image_url }}" alt="">@else<div class="ph"></div>@endif
                <div class="scrim"></div>
                <div class="in">
                    @if($featured->category)
                        <span class="tag" style="background:{{ $featured->category->color }}">{{ $featured->category->name }}</span>
                    @else
                        <span class="tag" style="background:var(--red)">Главное</span>
                    @endif
                    <h1>{{ $featured->displayTitle() }}</h1>
                    <p>{{ \Illuminate\Support\Str::limit(strip_tags($featured->edited_content), 180) }}</p>
                    <div class="by">{{ $featured->published_at?->diffForHumans() }} · источник: {{ $featured->source_name }}</div>
                </div>
            </a>
        @else
            <div class="splash">
                <div class="ph"></div><div class="scrim"></div>
                <div class="in">
                    <span class="tag" style="background:var(--red)">LiverpoolIn</span>
                    <h1>Пока нет опубликованных материалов</h1>
                    <p>Свежие новости «Ливерпуля» скоро появятся здесь. Загляните чуть позже. You'll never walk alone.</p>
                </div>
            </div>
        @endif
    </section>

    {{-- ============ ДАШБОРД ============ --}}
    <section class="dash">
        {{-- Расписание --}}
        <div class="panel">
            <div class="ph"><h2>Расписание</h2><a href="{{ route('fixtures') }}">Все →</a></div>
            <div class="bd">
                @forelse($upcoming as $f)
                    <div class="fxrow">
                        <div class="dt">
                            <div class="d">{{ $f->venueKickoff()?->format('d') ?? '—' }}</div>
                            <div class="m">{{ $f->venueKickoff()?->isoFormat('MMM') ?? '' }}</div>
                        </div>
                        <div class="tm">
                            <span class="{{ $f->isHome() ? 'us' : '' }}">{{ $f->home_team }}</span><br>
                            <span class="{{ ! $f->isHome() ? 'us' : '' }}">{{ $f->away_team }}</span>
                            <div class="cp">{{ $f->competitionName() }}</div>
                        </div>
                        <div class="tt">@include('public.partials.kickoff', ['f' => $f])</div>
                    </div>
                @empty
                    <div class="none">Ближайшие матчи не загружены.</div>
                @endforelse
            </div>
        </div>

        {{-- Результаты за месяц --}}
        <div class="panel">
            <div class="ph"><h2>Результаты</h2><a href="{{ route('fixtures', ['range' => 'all']) }}">Все →</a></div>
            <div class="bd">
                @forelse($recentResults as $f)
                    @php $o = $f->outcome(); @endphp
                    <div class="fxrow">
                        <div class="dt">
                            <div class="d">{{ $f->kickoff_at?->format('d') ?? '—' }}</div>
                            <div class="m">{{ $f->kickoff_at?->isoFormat('MMM') ?? '' }}</div>
                        </div>
                        <div class="tm">
                            <span class="{{ $f->isHome() ? 'us' : '' }}">{{ $f->home_team }}</span><br>
                            <span class="{{ ! $f->isHome() ? 'us' : '' }}">{{ $f->away_team }}</span>
                            <div class="cp">{{ $f->competitionName() }}</div>
                        </div>
                        <div class="sc {{ $o === 'win' ? 'w' : ($o === 'draw' ? 'd' : 'l') }}">
                            {{ $f->home_score }}:{{ $f->away_score }}
                        </div>
                    </div>
                @empty
                    <div class="none">За последний месяц сыгранных матчей нет.</div>
                @endforelse
            </div>
        </div>

        {{-- Таблица АПЛ --}}
        <div class="panel">
            <div class="ph"><h2>Таблица АПЛ</h2><a href="{{ route('table') }}">Вся →</a></div>
            <div class="bd scroll">
                @if($standings->isNotEmpty())
                    <table class="mini">
                        <thead><tr><th class="pos">#</th><th class="tm">Клуб</th><th>И</th><th>Р</th><th>О</th></tr></thead>
                        <tbody>
                        @foreach($standings as $r)
                            @php $zone = $r->position <= 4 ? 'ucl' : ($r->position === 5 ? 'uel' : ($r->position >= 18 ? 'rel' : '')); @endphp
                            <tr class="{{ $r->isLiverpool() ? 'lfc' : '' }}">
                                <td class="pos"><span class="zone {{ $zone }}"></span>{{ $r->position }}</td>
                                <td class="tm">@if($r->team_crest)<img class="crest" src="{{ $r->team_crest }}" alt="" loading="lazy">@endif{{ $r->team_short ?: $r->team_name }}</td>
                                <td>{{ $r->played }}</td>
                                <td>{{ $r->goal_difference > 0 ? '+' : '' }}{{ $r->goal_difference }}</td>
                                <td class="pts">{{ $r->points }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="none">Таблица пока не загружена.</div>
                @endif
            </div>
        </div>

        {{-- Таблица Лиги чемпионов --}}
        @if($clStandings->isNotEmpty())
            <div class="panel">
                <div class="ph"><h2>Лига чемпионов</h2><a href="{{ route('table', ['comp' => 'CL']) }}">Вся →</a></div>
                <div class="bd scroll">
                    <table class="mini">
                        <thead><tr><th class="pos">#</th><th class="tm">Клуб</th><th>И</th><th>Р</th><th>О</th></tr></thead>
                        <tbody>
                        @foreach($clStandings as $r)
                            <tr class="{{ $r->isLiverpool() ? 'lfc' : '' }}">
                                <td class="pos">{{ $r->position }}</td>
                                <td class="tm">@if($r->team_crest)<img class="crest" src="{{ $r->team_crest }}" alt="" loading="lazy">@endif{{ $r->team_short ?: $r->team_name }}</td>
                                <td>{{ $r->played }}</td>
                                <td>{{ $r->goal_difference > 0 ? '+' : '' }}{{ $r->goal_difference }}</td>
                                <td class="pts">{{ $r->points }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </section>

    {{-- ============ Новости ============ --}}
    <div class="cols">
        <div>
            <div class="sec-head"><h2>Свежее</h2><span class="rule"></span></div>
            @if($articles->isNotEmpty())
                <div class="grid">
                    @foreach($articles as $a)
                        <a class="story" href="{{ route('article.show', $a) }}">
                            <div class="thumb">
                                @if($a->image_url)<img src="{{ $a->image_url }}" alt="">@else<span class="mark">LFC</span>@endif
                                @if($a->category)
                                    <span class="tag" style="background:{{ $a->category->color }}">{{ $a->category->name }}</span>
                                @endif
                            </div>
                            <div class="body">
                                <span class="kicker">{{ $a->source_name ?: 'Новости' }}</span>
                                <h3>{{ $a->displayTitle() }}</h3>
                                <p class="ex">{{ \Illuminate\Support\Str::limit(strip_tags($a->edited_content), 110) }}</p>
                                <span class="time">{{ $a->published_at?->diffForHumans() }}</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="empty">Скоро здесь появятся новые материалы.</div>
            @endif
        </div>

        <aside class="side">
            <h2>Ближайшие матчи</h2>
            @if($upcoming->isNotEmpty())
                <div class="fx">
                    @foreach($upcoming as $f)
                        <div class="item">
                            <div class="comp">{{ $f->competitionName() }}</div>
                            <div class="teams">{{ $f->home_team }} — {{ $f->away_team }}</div>
                            <div class="when">
                                {{ $f->venueKickoff()?->isoFormat('dd, D MMMM YYYY') ?? 'Дата уточняется' }}
                                <div style="margin-top:4px">@include('public.partials.kickoff', ['f' => $f, 'compact' => true])</div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <a class="allfx" href="{{ route('fixtures') }}">Полное расписание →</a>
            @else
                <div class="empty">Матчи пока не запланированы.</div>
            @endif
        </aside>
    </div>
</main>

@include('public.partials.footer')
</body>
</html>
