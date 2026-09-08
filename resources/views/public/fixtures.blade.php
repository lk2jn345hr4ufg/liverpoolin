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

        /* Календарь / диапазон дат */
        .cal{background:var(--card);border:1px solid var(--line);border-radius:8px;padding:14px 16px;margin:22px 0 0}
        .cal .presets{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px}
        .cal form{display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap}
        .cal .fld{display:flex;flex-direction:column;gap:4px}
        .cal label{font-size:11px;font-weight:800;letter-spacing:.8px;text-transform:uppercase;color:var(--muted)}
        .cal input[type=date]{border:1px solid var(--line);border-radius:6px;padding:8px 10px;font:inherit;
             background:var(--paper);color:var(--ink);min-width:150px}
        .cal .go{background:var(--red);color:#fff;border:0;border-radius:6px;padding:9px 16px;
             font-weight:800;font-size:13px;text-transform:uppercase;letter-spacing:.6px;cursor:pointer}
        .cal .go:hover{background:var(--wine)}

        /* Навигация по сезонам (еврокубки) */
        .seasonnav{display:flex;align-items:center;gap:6px;margin:22px 0 0;flex-wrap:wrap}
        .seasonnav .arrow{width:34px;height:34px;border-radius:6px;border:1px solid var(--line);
             background:var(--card);display:flex;align-items:center;justify-content:center;font-size:16px;font-weight:800;flex:0 0 auto}
        .seasonnav .arrow:hover{border-color:var(--red);color:var(--red)}
        .seasonnav .arrow.off{opacity:.35;pointer-events:none}
        .seasonnav .cur{font-family:var(--display);font-size:22px;letter-spacing:1px;padding:0 12px;min-width:120px;text-align:center}
        .seasonnav .pick{display:flex;gap:6px;flex-wrap:wrap;margin-left:8px}

        .summary{display:grid;grid-template-columns:repeat(5,1fr);gap:8px;margin:18px 0 0}
        .sbox{background:var(--card);border:1px solid var(--line);border-radius:6px;padding:12px 8px;text-align:center}
        .sbox .n{font-family:var(--display);font-size:24px;line-height:1}
        .sbox .l{font-size:9.5px;font-weight:800;letter-spacing:1px;text-transform:uppercase;color:var(--muted);margin-top:5px}
        .sbox.w .n{color:#1a7f4b} .sbox.dr .n{color:#b8901f} .sbox.ls .n{color:var(--red)}

        .filters{display:flex;gap:8px;flex-wrap:wrap;margin:18px 0 0}

        .month{display:flex;align-items:baseline;gap:12px;margin:28px 0 12px}
        .month h2{font-family:var(--display);font-weight:400;text-transform:uppercase;font-size:17px;letter-spacing:1.2px;white-space:nowrap}
        .month .rule{flex:1;height:2px;background:var(--line)}
        .month .cnt{font-size:11px;font-weight:800;color:var(--muted);letter-spacing:.5px}

        .fx{display:flex;flex-direction:column;gap:8px}
        .m{background:var(--card);border:1px solid var(--line);border-left:4px solid #d9d3c9;border-radius:5px;padding:12px 14px;display:flex;align-items:center;gap:14px}
        .m.next{border-left-color:var(--gold);background:#fffdf6}
        .m.win{border-left-color:#1a7f4b} .m.draw{border-left-color:#c9a227} .m.loss{border-left-color:var(--red)}
        .m .date{flex:0 0 64px;text-align:center;line-height:1.15}
        .m .date .dd{font-family:var(--display);font-size:20px;letter-spacing:.5px}
        .m .date .mm{font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.8px;color:var(--muted)}
        .m .date .tt{font-size:11px;font-weight:700;color:var(--muted);margin-top:3px}
        .m .mid{flex:1 1 auto;min-width:0}
        .m .teams{display:flex;align-items:center;gap:9px;flex-wrap:wrap;font-size:15px;font-weight:700;line-height:1.3}
        .m .teams .t{min-width:0;overflow-wrap:anywhere}
        .m .teams .us{color:var(--red)}
        .m .sc{font-family:var(--display);font-size:17px;letter-spacing:1px;background:var(--wine);color:#fff;border-radius:3px;padding:1px 8px;flex:0 0 auto}
        .m .vs{color:#b9b0a6;font-weight:800;flex:0 0 auto}
        .m .sub{margin-top:5px;display:flex;gap:8px;flex-wrap:wrap;align-items:center}
        .m .comp{font-size:10px;font-weight:800;letter-spacing:1.1px;text-transform:uppercase;color:var(--muted)}
        .m .side{flex:0 0 auto;margin-left:auto}
        .flag{font-size:9.5px;font-weight:800;letter-spacing:.8px;text-transform:uppercase;padding:3px 8px;border-radius:99px;white-space:nowrap}
        .flag.h{background:#eaf1ff;color:#2b4d8f} .flag.a{background:#f3efe8;color:#7a6a58}
        .flag.pp{background:#fee2e2;color:#991b1b} .flag.nx{background:var(--gold);color:var(--wine-2)}

        @media(max-width:640px){
            .summary{grid-template-columns:repeat(3,1fr)}
            .cal form{flex-direction:column;align-items:stretch}
            .cal input[type=date]{width:100%}
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

    {{-- Календарь: пресеты + произвольный диапазон дат --}}
    @if($showCalendar)
        @php
            $today = now();
            $p3 = ['from' => $today->toDateString(), 'to' => $today->copy()->addMonths(3)->toDateString()];
            $pMonth = ['from' => $today->toDateString(), 'to' => $today->copy()->addMonth()->toDateString()];
            $pPast = ['from' => $today->copy()->subMonths(3)->toDateString(), 'to' => $today->toDateString()];
            $curFrom = request('from');
            $curTo = request('to');
            $isDefault = ! $isAll && ! $curFrom && ! $curTo;
        @endphp
        <div class="cal">
            <div class="presets">
                <a class="chip {{ $isDefault ? 'on' : '' }}"
                   href="{{ route('fixtures', array_merge(['comp' => $activeComp], $p3)) }}">3 месяца вперёд</a>
                <a class="chip {{ $curFrom === $pMonth['from'] && $curTo === $pMonth['to'] ? 'on' : '' }}"
                   href="{{ route('fixtures', array_merge(['comp' => $activeComp], $pMonth)) }}">Ближайший месяц</a>
                <a class="chip {{ $curFrom === $pPast['from'] && $curTo === $pPast['to'] ? 'on' : '' }}"
                   href="{{ route('fixtures', array_merge(['comp' => $activeComp], $pPast)) }}">Прошедшие 3 месяца</a>
                <a class="chip {{ $isAll ? 'on' : '' }}"
                   href="{{ route('fixtures', ['comp' => $activeComp, 'range' => 'all']) }}">Весь сезон</a>
            </div>

            <form method="get" action="{{ route('fixtures') }}">
                @if($activeComp)<input type="hidden" name="comp" value="{{ $activeComp }}">@endif
                <div class="fld">
                    <label for="from">С даты</label>
                    <input type="date" id="from" name="from" value="{{ $curFrom ?: $fromDate->toDateString() }}">
                </div>
                <div class="fld">
                    <label for="to">По дату</label>
                    <input type="date" id="to" name="to" value="{{ $curTo ?: $toDate->toDateString() }}">
                </div>
                <button class="go" type="submit">Показать</button>
            </form>
        </div>
    @endif

    {{-- Навигация по сезонам — только на еврокубках --}}
    @if($isIntl && $seasons->isNotEmpty())
        @php
            $idx   = $seasons->search($activeSeason);
            $newer = $idx > 0 ? $seasons[$idx - 1] : null;
            $older = $idx < $seasons->count() - 1 ? $seasons[$idx + 1] : null;
        @endphp
        <div class="seasonnav">
            <a class="arrow {{ $older === null ? 'off' : '' }}"
               href="{{ $older !== null ? route('fixtures.international', ['season' => $older, 'comp' => $activeComp]) : '#' }}">‹</a>
            <span class="cur">{{ $activeSeason }}/{{ substr((string)($activeSeason + 1), 2) }}</span>
            <a class="arrow {{ $newer === null ? 'off' : '' }}"
               href="{{ $newer !== null ? route('fixtures.international', ['season' => $newer, 'comp' => $activeComp]) : '#' }}">›</a>
            @if($seasons->count() > 1)
                <span class="pick">
                    @foreach($seasons as $s)
                        <a class="chip {{ $s === $activeSeason ? 'on' : '' }}"
                           href="{{ route('fixtures.international', ['season' => $s, 'comp' => $activeComp]) }}">
                            {{ $s }}/{{ substr((string)($s + 1), 2) }}
                        </a>
                    @endforeach
                </span>
            @endif
        </div>
    @endif

    @if($stats['total'] > 0)
        <div class="summary">
            <div class="sbox"><div class="n">{{ $stats['total'] }}</div><div class="l">Матчей</div></div>
            <div class="sbox"><div class="n">{{ $stats['upcoming'] }}</div><div class="l">Впереди</div></div>
            <div class="sbox w"><div class="n">{{ $stats['win'] }}</div><div class="l">Победы</div></div>
            <div class="sbox dr"><div class="n">{{ $stats['draw'] }}</div><div class="l">Ничьи</div></div>
            <div class="sbox ls"><div class="n">{{ $stats['loss'] }}</div><div class="l">Поражения</div></div>
        </div>
    @endif

    {{-- Фильтр по турниру внутри сезона (еврокубки) --}}
    @if($isIntl && $intlComps->count() > 1)
        <div class="filters">
            <a class="chip {{ empty($activeComp) ? 'on' : '' }}"
               href="{{ route('fixtures.international', ['season' => $activeSeason]) }}">Все турниры</a>
            @foreach($intlComps as $c)
                <a class="chip {{ $activeComp === $c ? 'on' : '' }}"
                   href="{{ route('fixtures.international', ['season' => $activeSeason, 'comp' => $c]) }}">
                    {{ \App\Models\Fixture::COMPETITION_NAMES[$c] ?? $c }}
                </a>
            @endforeach
        </div>
    @endif

    {{-- Фильтр по турниру для обычного расписания --}}
    @if(! $isIntl && count($competitions))
        <div class="filters">
            <a class="chip {{ empty($activeComp) ? 'on' : '' }}"
               href="{{ route('fixtures', array_filter(['from' => request('from'), 'to' => request('to'), 'range' => request('range')])) }}">Все турниры</a>
            @foreach($competitions as $c)
                <a class="chip {{ $activeComp === $c ? 'on' : '' }}"
                   href="{{ route('fixtures', array_filter(['comp' => $c, 'from' => request('from'), 'to' => request('to'), 'range' => request('range')])) }}">
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
                            @if($f->matchday)<span class="comp">· {{ $f->matchday }} тур</span>@endif
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
                @if($seasons->isEmpty())
                    Еврокубковых матчей пока нет в базе. Загрузите их в админке («🏆 Еврокубки за сезон»).
                @else
                    В сезоне {{ $activeSeason }}/{{ substr((string)($activeSeason + 1), 2) }} матчей нет.
                @endif
            @else
                В выбранном диапазоне матчей нет. Измените даты выше или нажмите «Весь сезон».
            @endif
        </div>
    @endforelse
</div>

@include('public.partials.footer')
</body>
</html>
