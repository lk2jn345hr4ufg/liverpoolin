<!DOCTYPE html>
<html lang="ru">
<head>
    @include('public.partials.head', ['title' => 'Трансферы — LiverpoolIn'])
    <style>
        .page{max-width:900px;margin:0 auto;padding:0 20px}
        .pagehead{padding:30px 0 4px}
        .pagehead h1{font-family:var(--display);font-weight:400;text-transform:uppercase;
                     font-size:clamp(26px,4.4vw,42px);letter-spacing:1px;line-height:1}
        .pagehead .intro{color:var(--muted);font-size:15px;max-width:620px;margin-top:10px}

        .counts{display:grid;grid-template-columns:repeat(2,1fr);gap:10px;margin:22px 0 0;max-width:340px}
        .cbox{background:var(--card);border:1px solid var(--line);border-radius:6px;padding:12px;text-align:center}
        .cbox .n{font-family:var(--display);font-size:26px;line-height:1}
        .cbox.in .n{color:#1a7f4b} .cbox.out .n{color:var(--red)}
        .cbox .l{font-size:10px;font-weight:800;letter-spacing:1px;text-transform:uppercase;color:var(--muted);margin-top:5px}

        .filters{display:flex;gap:8px;flex-wrap:wrap;margin:20px 0 0}
        .filters .grp{display:flex;gap:8px;flex-wrap:wrap}
        .filters .sep{width:1px;background:var(--line);margin:0 4px}

        .month{display:flex;align-items:baseline;gap:12px;margin:28px 0 12px}
        .month h2{font-family:var(--display);font-weight:400;text-transform:uppercase;
                  font-size:17px;letter-spacing:1.2px;white-space:nowrap}
        .month .rule{flex:1;height:2px;background:var(--line)}

        .list{display:flex;flex-direction:column;gap:8px}
        .t{background:var(--card);border:1px solid var(--line);border-left:4px solid #d9d3c9;
           border-radius:5px;padding:13px 15px;display:flex;align-items:center;gap:14px;flex-wrap:wrap}
        .t.in{border-left-color:#1a7f4b}
        .t.out{border-left-color:var(--red)}

        .t .arrow{flex:0 0 30px;text-align:center;font-size:19px;font-weight:800;line-height:1}
        .t.in .arrow{color:#1a7f4b}
        .t.out .arrow{color:var(--red)}

        .t .mid{flex:1 1 240px;min-width:0}
        .t .name{font-size:16px;font-weight:800;line-height:1.25;overflow-wrap:anywhere}
        .t .route{margin-top:4px;font-size:13px;color:var(--muted);display:flex;align-items:center;gap:7px;flex-wrap:wrap}
        .t .route img{width:16px;height:16px;object-fit:contain;flex:0 0 auto}
        .t .route .to{color:var(--ink);font-weight:700}

        .t .right{flex:0 0 auto;margin-left:auto;text-align:right;display:flex;flex-direction:column;gap:5px;align-items:flex-end}
        .t .date{font-size:11px;font-weight:700;color:var(--muted);white-space:nowrap}
        .kind{font-size:9.5px;font-weight:800;letter-spacing:.8px;text-transform:uppercase;
              padding:3px 8px;border-radius:99px;white-space:nowrap}
        .kind.fee{background:var(--wine);color:#fff}
        .kind.loan{background:#eaf1ff;color:#2b4d8f}
        .kind.free{background:#f3efe8;color:#7a6a58}
        .kind.unknown{background:#f0eee9;color:#95897c}

        @media(max-width:640px){
            .t .right{margin-left:0;flex:1 1 100%;flex-direction:row;justify-content:flex-start;align-items:center}
        }
    </style>
</head>
<body>

@include('public.partials.header', ['activeCat' => null])

<div class="page">
    <div class="pagehead">
        <h1>Трансферы</h1>
        <p class="intro">Приходы и уходы «Ливерпуля»: игрок, клуб, тип перехода и сумма, если она известна.</p>
    </div>

    @if($transfers->isNotEmpty() || $countIn || $countOut)
        <div class="counts">
            <div class="cbox in"><div class="n">{{ $countIn }}</div><div class="l">Пришли</div></div>
            <div class="cbox out"><div class="n">{{ $countOut }}</div><div class="l">Ушли</div></div>
        </div>
    @endif

    @if($seasons->isNotEmpty())
        <div class="filters">
            <div class="grp">
                <a class="chip {{ empty($dir) ? 'on' : '' }}"
                   href="{{ route('transfers', ['season' => $season]) }}">Все</a>
                <a class="chip {{ $dir === 'in' ? 'on' : '' }}"
                   href="{{ route('transfers', ['season' => $season, 'dir' => 'in']) }}">Приходы</a>
                <a class="chip {{ $dir === 'out' ? 'on' : '' }}"
                   href="{{ route('transfers', ['season' => $season, 'dir' => 'out']) }}">Уходы</a>
            </div>

            <div class="sep"></div>

            <div class="grp">
                <a class="chip {{ empty($season) ? 'on' : '' }}"
                   href="{{ route('transfers', ['dir' => $dir]) }}">Все сезоны</a>
                @foreach($seasons as $s)
                    <a class="chip {{ $season === $s ? 'on' : '' }}"
                       href="{{ route('transfers', ['season' => $s, 'dir' => $dir]) }}">
                        {{ $s }}/{{ substr((string)($s + 1), 2) }}
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    @forelse($grouped as $month => $items)
        <div class="month">
            <h2>{{ $month }}</h2>
            <span class="rule"></span>
        </div>

        <div class="list">
            @foreach($items as $t)
                <div class="t {{ $t->direction }}">
                    <div class="arrow">{{ $t->isArrival() ? '↓' : '↑' }}</div>

                    <div class="mid">
                        <div class="name">{{ $t->player_name }}</div>
                        <div class="route">
                            @if($t->club_from_logo)<img src="{{ $t->club_from_logo }}" alt="" loading="lazy">@endif
                            <span>{{ $t->club_from ?: '—' }}</span>
                            <span>→</span>
                            @if($t->club_to_logo)<img src="{{ $t->club_to_logo }}" alt="" loading="lazy">@endif
                            <span class="to">{{ $t->club_to ?: '—' }}</span>
                        </div>
                    </div>

                    <div class="right">
                        <span class="kind {{ $t->kind }}">
                            {{ $t->feeLabel() ?: $t->kindLabel() }}
                        </span>
                        <span class="date">{{ $t->transfer_date?->isoFormat('D MMM YYYY') ?? '' }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    @empty
        <div class="empty" style="margin-top:26px">
            Данные о трансферах пока не загружены.
            Запустите синхронизацию: <code>php artisan transfers:sync</code>
            (нужен ключ API-Football в админке).
        </div>
    @endforelse
</div>

@include('public.partials.footer')
</body>
</html>
