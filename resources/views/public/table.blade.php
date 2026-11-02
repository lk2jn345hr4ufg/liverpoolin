<!DOCTYPE html>
<html lang="ru">
<head>
    @include('public.partials.head', ['title' => 'Турнирные таблицы — LiverpoolIn'])
    <style>
        .narrow{max-width:940px;margin:0 auto;padding:0 20px}
        .pagehead{padding:28px 0 6px}
        .pagehead h1{font-family:var(--display);font-weight:400;text-transform:uppercase;font-size:clamp(28px,4.6vw,44px);letter-spacing:1px}
        .pagehead .intro{color:var(--muted);font-size:15px;max-width:660px;margin-top:8px}

        .picker{display:flex;gap:8px;flex-wrap:wrap;margin:18px 0 0}

        .tablecard{background:var(--card);border:1px solid var(--line);border-radius:6px;overflow:auto;margin-top:18px}
        table.lg{width:100%;border-collapse:collapse;min-width:520px}
        table.lg th{font-size:10px;font-weight:800;letter-spacing:1.2px;text-transform:uppercase;color:var(--muted);text-align:center;padding:11px 6px;border-bottom:2px solid var(--line);background:#faf7f2}
        table.lg th.team{text-align:left;padding-left:14px}
        table.lg td{padding:10px 6px;text-align:center;border-bottom:1px solid var(--line);font-size:14px;font-variant-numeric:tabular-nums}
        table.lg td.team{text-align:left;padding-left:14px;font-weight:700}
        table.lg tr:last-child td{border-bottom:0}
        table.lg tr.lfc{background:rgba(200,16,46,.07)}
        table.lg tr.lfc td.team{color:var(--red)}
        .pos{font-family:var(--display);font-size:16px;width:34px}
        .zone{display:inline-block;width:3px;height:22px;border-radius:2px;vertical-align:middle;margin-right:8px;background:transparent}
        .zone.ucl{background:#1a56b8} .zone.uel{background:#f5b22d} .zone.relegation{background:var(--red)}
        .pts{font-weight:800}
        .crest{width:18px;height:18px;object-fit:contain;vertical-align:middle;margin-right:7px;display:inline-block}
        .form{display:inline-flex;gap:3px}
        .form i{width:16px;height:16px;border-radius:3px;font-style:normal;font-size:9px;font-weight:800;display:flex;align-items:center;justify-content:center;color:#fff}
        .form i.W{background:#1a7f4b} .form i.D{background:#b8991f} .form i.L{background:var(--red)}
        .legend{display:flex;gap:16px;flex-wrap:wrap;margin:12px 2px 0;font-size:12px;color:var(--muted)}
        .legend span{display:flex;align-items:center;gap:6px}
        .legend i{width:10px;height:10px;border-radius:2px;display:inline-block}
        @media(max-width:700px){table.lg td.hide,table.lg th.hide{display:none}}
    </style>
</head>
<body>

@include('public.partials.header', ['activeCat' => null])

<div class="narrow">
    <div class="pagehead">
        <h1>Турнирные таблицы</h1>
        <p class="intro">
            @if($code === 'PL') Английская Премьер-лига @else {{ \App\Models\Standing::compName($code) }} @endif
            @if($season) — сезон {{ $season }}/{{ substr((string)($season + 1), 2) }}@if($matchday), после {{ $matchday }}-го тура@endif.@endif
        </p>
    </div>

    {{-- Переключатель турниров (АПЛ / еврокубки) --}}
    @if($comps->count() > 1)
        <div class="picker">
            @foreach($comps as $c)
                <a class="chip {{ $c === $code ? 'on' : '' }}" href="{{ route('table', ['comp' => $c]) }}">
                    {{ \App\Models\Standing::compName($c) }}
                </a>
            @endforeach
        </div>
    @endif

    {{-- Сезоны выбранного турнира --}}
    @if($seasons->count() > 1)
        <div class="picker">
            @foreach($seasons as $s)
                <a class="chip {{ $s === $season ? 'on' : '' }}" href="{{ route('table', ['comp' => $code, 'season' => $s]) }}">{{ $s }}/{{ substr((string)($s + 1), 2) }}</a>
            @endforeach
        </div>
    @endif

    @if($rows->isEmpty())
        <div class="empty" style="margin-top:24px">
            Таблица пока не загружена. Запустите синхронизацию:
            <code>php artisan standings:sync</code> (для еврокубков — через загрузку соответствующего турнира).
        </div>
    @else
        <div class="tablecard">
            <table class="lg">
                <thead>
                    <tr>
                        <th>#</th><th class="team">Команда</th><th>И</th>
                        <th class="hide">В</th><th class="hide">Н</th><th class="hide">П</th>
                        <th class="hide">Мячи</th><th>Р</th><th>О</th><th class="hide">Форма</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($rows as $r)
                    <tr class="{{ $r->isLiverpool() ? 'lfc' : '' }}">
                        <td class="pos">
                            @if($code === 'PL')<span class="zone {{ $r->zone() }}"></span>@endif{{ $r->position }}
                        </td>
                        <td class="team">
                            @if($r->team_crest)<img class="crest" src="{{ $r->team_crest }}" alt="" loading="lazy">@endif
                            {{ $r->team_short ?: $r->team_name }}
                        </td>
                        <td>{{ $r->played }}</td>
                        <td class="hide">{{ $r->won }}</td>
                        <td class="hide">{{ $r->draw }}</td>
                        <td class="hide">{{ $r->lost }}</td>
                        <td class="hide">{{ $r->goals_for }}:{{ $r->goals_against }}</td>
                        <td>{{ $r->goal_difference > 0 ? '+' : '' }}{{ $r->goal_difference }}</td>
                        <td class="pts">{{ $r->points }}</td>
                        <td class="hide">
                            <span class="form">
                                @foreach($r->formArray() as $x)
                                    <i class="{{ $x }}">{{ $x === 'W' ? 'В' : ($x === 'D' ? 'Н' : 'П') }}</i>
                                @endforeach
                            </span>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        @if($code === 'PL')
            <div class="legend">
                <span><i style="background:#1a56b8"></i> Лига чемпионов</span>
                <span><i style="background:#f5b22d"></i> Лига Европы</span>
                <span><i style="background:#c8102e"></i> Вылет</span>
            </div>
        @endif
    @endif
</div>

@include('public.partials.footer')
</body>
</html>
