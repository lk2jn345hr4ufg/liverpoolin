<!DOCTYPE html>
<html lang="ru">
<head>
    @include('public.partials.head', ['title' => $title . ' — LiverpoolIn'])
    <style>
        .page{max-width:900px;margin:0 auto;padding:0 20px}

        .pagehead{padding:30px 0 4px}
        .pagehead h1{font-family:var(--display);font-weight:400;text-transform:uppercase;
                     font-size:clamp(26px,4.4vw,42px);letter-spacing:1px;line-height:1}
        .pagehead .intro{color:var(--muted);font-size:15px;max-width:620px;margin-top:10px}

        .summary{display:grid;grid-template-columns:repeat(5,1fr);gap:8px;margin:22px 0 0}
        .sbox{background:var(--card);border:1px solid var(--line);border-radius:6px;padding:12px 8px;text-align:center}
        .sbox .n{font-family:var(--display);font-size:24px;line-height:1}
        .sbox .l{font-size:9.5px;font-weight:800;letter-spacing:1px;text-transform:uppercase;color:var(--muted);margin-top:5px}
        .sbox.w .n{color:#1a7f4b} .sbox.dr .n{color:#b8901f} .sbox.ls .n{color:var(--red)}

        .filters{display:flex;gap:8px;flex-wrap:wrap;margin:22px 0 0}

        .month{display:flex;align-items:baseline;gap:12px;margin:30px 0 12px}
        .month h2{font-family:var(--display);font-weight:400;text-transform:uppercase;
                  font-size:17px;letter-spacing:1.2px;white-space:nowrap}
        .month .rule{flex:1;height:2px;background:var(--line)}
        .month .cnt{font-size:11px;font-weight:800;color:var(--muted);letter-spacing:.5px}

        .fx{display:flex;flex-direction:column;gap:8px}

        .m{background:var(--card);border:1px solid var(--line);border-left:4px solid #d9d3c9;
           border-radius:5px;padding:12px 14px;display:flex;align-items:center;gap:14px}
        .m.next{border-left-color:var(--gold);background:#fffdf6}
        .m.win{border-left-color:#1a7f4b}
        .m.draw{border-left-color:#c9a227}
        .m.loss{border-left-color:var(--red)}

        .m .date{flex:0 0 64px;text-align:center;line-height:1.15}
        .m .date .dd{font-family:var(--display);font-size:20px;letter-spacing:.5px}
        .m .date .mm{font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.8px;color:var(--muted)}
        .m .date .tt{font-size:11px;font-weight:700;color:var(--muted);margin-top:3px}

        .m .mid{flex:1 1 auto;min-width:0}
        .m .teams{display:flex;align-items:center;gap:9px;flex-wrap:wrap;font-size:15px;font-weight:700;line-height:1.3}
        .m .teams .t{min-width:0;overflow-wrap:anywhere}
        .m .teams .us{color:var(--red)}
        .m .sc{font-family:var(--display);font-size:17px;letter-spacing:1px;
               background:var(--wine);color:#fff;border-radius:3px;padding:1px 8px;flex:0 0 auto}
        .m .vs{color:#b9b0a6;font-weight:800;flex:0 0 auto}
        .m .sub{margin-top:5px;display:flex;gap:8px;flex-wrap:wrap;align-items:center}
        .m .comp{font-size:10px;font-weight:800;letter-spacing:1.1px;text-transform:uppercase;color:var(--muted)}

        .m .side{flex:0 0 auto;margin-left:auto}
        .flag{font-size:9.5px;font-weight:800;letter-spacing:.8px;text-transform:uppercase;
              padding:3px 8px;border-radius:99px;white-space:nowrap}
        .flag.h{background:#eaf1ff;color:#2b4d8f}
        .flag.a{background:#f3efe8;color:#7a6a58}
        .flag.pp{background:#fee2e2;color:#991b1b}
        .flag.nx{background:var(--gold);color:var(--wine-2)}

        @media(max-width:640px){
            .summary{grid-template-columns:repeat(3,1fr)}
            .m{flex-wrap:wrap;gap:10px}
            .m .date{flex:0 0 auto;text-align:left;display:flex;align-items:baseline;gap:6px}
            .m .date .tt{margin-top:0}
            .m .mid{flex:1 1 100%;order:3}
            .m .side{order:2}
        }
    </style>
</head>
<body>

@include('public.partials.header', ['activeCat' => null])

<div class="page">
    <div class="pagehead">
        <h1>{{ $title }}</h1>
        <p class="intro">{{ $intro }}</p>
    </div>

    @if($stats['total'] > 0)
        <div class="summary">
            <div class="sbox"><div class="n">{{ $stats['total'] }}</div><div class="l">Всего</div></div>
            <div class="sbox"><div class="n">{{ $stats['upcoming'] }}</div><div class="l">Впереди</div></div>
            <div class="sbox w"><div class="n">{{ $stats['win'] }}</div><div class="l">Победы</div></div>
            <div class="sbox dr"><div class="n">{{ $stats['draw'] }}</div><div class="l">Ничьи</div></div>
            <div class="sbox ls"><div class="n">{{ $stats['loss'] }}</div><div class="l">Поражения</div></div>
        </div>
    @endif

    @if(! $isIntl && count($competitions))
        <div class="filters">
            <a class="chip {{ empty($activeComp) ? 'on' : '' }}" href="{{ route('fixtures') }}">Все турниры</a>
            @foreach($competitions as $c)
                <a class="chip {{ $activeComp === $c ? 'on' : '' }}" href="{{ route('fixtures', ['comp' => $c]) }}">
                    {{ \App\Models\Fixture::COMPETITION_NAMES[$c] ?? $c }}
                </a>
            @endforeach
        </div>
    @endif

    @forelse($grouped as $month => $items)
        <div class="month">
            <h2>{{ $month }}</h2>
            <span class="rule"></span>
            <span class="cnt">{{ $items->count() }}</span>
        </div>

        <div class="fx">
            @foreach($items as $f)
                @php
                    $isNext  = $nextFixture && $nextFixture->id === $f->id;
                    $outcome = $f->outcome();
                    $cls     = $outcome ?: ($isNext ? 'next' : '');
                @endphp
                <div class="m {{ $cls }}">
                    <div class="date">
                        {{-- isoFormat использует токены в стиле Moment.js и корректно
                             локализуется; translatedFormat ждёт буквы PHP date(). --}}
                        <div class="dd">{{ $f->kickoff_at?->format('d') ?? '—' }}</div>
                        <div class="mm">{{ $f->kickoff_at?->isoFormat('MMM') ?? '' }}</div>
                        <div class="tt">{{ $f->kickoff_at?->format('H:i') ?? 'TBC' }}</div>
                    </div>

                    <div class="mid">
                        <div class="teams">
                            <span class="t {{ $f->isHome() ? 'us' : '' }}">{{ $f->home_team }}</span>
                            @if($f->hasResult())
                                <span class="sc">{{ $f->home_score }}:{{ $f->away_score }}</span>
                            @else
                                <span class="vs">—</span>
                            @endif
                            <span class="t {{ ! $f->isHome() ? 'us' : '' }}">{{ $f->away_team }}</span>
                        </div>
                        <div class="sub">
                            <span class="comp">{{ $f->competitionName() }}</span>
                            @if($f->matchday)
                                <span class="comp">· {{ $f->matchday }} тур</span>
                            @endif
                        </div>
                    </div>

                    <div class="side">
                        @if($f->status === 'postponed')
                            <span class="flag pp">Перенесён</span>
                        @elseif($isNext)
                            <span class="flag nx">Следующий</span>
                        @else
                            <span class="flag {{ $f->isHome() ? 'h' : 'a' }}">{{ $f->isHome() ? 'Дома' : 'В гостях' }}</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @empty
        <div class="empty" style="margin-top:26px">
            @if($isIntl)
                Международных матчей пока нет — они появятся со стартом еврокубков.
            @else
                Расписание пустое. Запустите синхронизацию: <code>php artisan fixtures:sync</code>
            @endif
        </div>
    @endforelse
</div>

@include('public.partials.footer')
</body>
</html>
