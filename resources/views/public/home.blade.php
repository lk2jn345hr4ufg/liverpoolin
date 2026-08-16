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
                {{ $nextFixture->kickoff_at?->isoFormat('dd, D MMM · HH:mm') ?? 'Дата уточняется' }}
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
            @if($fixtures->isNotEmpty())
                <div class="fx">
                    @foreach($fixtures as $f)
                        <div class="item">
                            <div class="comp">{{ $f->competitionName() }}</div>
                            <div class="teams">{{ $f->home_team }} — {{ $f->away_team }}</div>
                            <div class="when">{{ $f->kickoff_at?->isoFormat('dd, D MMMM YYYY · HH:mm') ?? 'Дата уточняется' }}</div>
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
