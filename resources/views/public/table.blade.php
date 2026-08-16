<!DOCTYPE html>
<html lang="ru">
<head>
    @include('public.partials.head', ['title' => 'Таблица АПЛ — LiverpoolIn'])
    <style>
        .narrow{max-width:940px;margin:0 auto;padding:0 20px}
        .pagehead{padding:28px 0 6px}
        .pagehead h1{font-family:var(--display);font-weight:400;text-transform:uppercase;font-size:clamp(28px,4.6vw,44px);letter-spacing:1px}
        .pagehead .intro{color:var(--muted);font-size:15px;max-width:660px;margin-top:8px}

        .seasons{display:flex;gap:8px;flex-wrap:wrap;margin:20px 0 6px}

        .tablecard{background:var(--card);border:1px solid var(--line);border-radius:6px;overflow:hidden;margin-top:18px}
        table.lg{width:100%;border-collapse:collapse}
        table.lg th{font-size:10px;font-weight:800;letter-spacing:1.2px;text-transform:uppercase;color:var(--muted);
                    text-align:center;padding:11px 6px;border-bottom:2px solid var(--line);background:#faf7f2}
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
        .form i{width:16px;height:16px;border-radius:3px;font-style:normal;font-size:9px;font-weight:800;
                display:flex;align-items:center;justify-content:center;color:#fff}
        .form i.W{background:#1a7f4b} .form i.D{background:#b8991f} .form i.L{background:var(--red)}

        .legend{display:flex;gap:16px;flex-wrap:wrap;margin:12px 2px 0;font-size:12px;color:var(--muted)}
        .legend span{display:flex;align-items:center;gap:6px}
        .legend i{width:10px;height:10px;border-radius:2px;display:inline-block}

        .chartcard{background:var(--card);border:1px solid var(--line);border-radius:6px;padding:18px;margin-top:22px}
        .chartcard h3{font-family:var(--display);font-weight:400;text-transform:uppercase;font-size:17px;letter-spacing:1px;margin-bottom:4px}
        .chartcard .sub{font-size:13px;color:var(--muted);margin-bottom:14px}

        @media(max-width:700px){
            table.lg td.hide,table.lg th.hide{display:none}
            table.lg td{padding:9px 4px;font-size:13px}
        }
    </style>
</head>
<body>

@include('public.partials.header', ['activeCat' => null])

<div class="narrow">
    <div class="pagehead">
        <h1>Таблица АПЛ</h1>
        <p class="intro">
            Турнирная таблица Премьер-лиги
            @if($season) — сезон {{ $season }}/{{ substr((string)($season + 1), 2) }}@if($matchday), после {{ $matchday }}-го тура@endif.@endif
        </p>
    </div>

    @if($seasons->count() > 1)
        <div class="seasons">
            @foreach($seasons as $s)
                <a class="chip {{ $s === $season ? 'on' : '' }}"
                   href="{{ route('table', ['season' => $s]) }}">{{ $s }}/{{ substr((string)($s + 1), 2) }}</a>
            @endforeach
        </div>
    @endif

    @if($rows->isEmpty())
        <div class="empty" style="margin-top:24px">
            Таблица пока не загружена. Запустите синхронизацию:
            <code>php artisan standings:sync</code><br>
            Архив прошлых сезонов: <code>php artisan standings:sync --from=2020 --to=2026</code>
        </div>
    @else
        @if($lfc)
            <div class="summary" style="display:flex;gap:10px;flex-wrap:wrap;margin:18px 0 0">
                <div class="sbox" style="background:var(--card);border:1px solid var(--line);border-radius:6px;padding:12px 16px;min-width:88px;text-align:center">
                    <div style="font-family:var(--display);font-size:26px;line-height:1;color:var(--red)">{{ $lfc->position }}</div>
                    <div style="font-size:10px;font-weight:800;letter-spacing:1.2px;text-transform:uppercase;color:var(--muted);margin-top:4px">Место</div>
                </div>
                <div class="sbox" style="background:var(--card);border:1px solid var(--line);border-radius:6px;padding:12px 16px;min-width:88px;text-align:center">
                    <div style="font-family:var(--display);font-size:26px;line-height:1">{{ $lfc->points }}</div>
                    <div style="font-size:10px;font-weight:800;letter-spacing:1.2px;text-transform:uppercase;color:var(--muted);margin-top:4px">Очки</div>
                </div>
                <div class="sbox" style="background:var(--card);border:1px solid var(--line);border-radius:6px;padding:12px 16px;min-width:88px;text-align:center">
                    <div style="font-family:var(--display);font-size:26px;line-height:1">{{ $lfc->won }}–{{ $lfc->draw }}–{{ $lfc->lost }}</div>
                    <div style="font-size:10px;font-weight:800;letter-spacing:1.2px;text-transform:uppercase;color:var(--muted);margin-top:4px">В–Н–П</div>
                </div>
                <div class="sbox" style="background:var(--card);border:1px solid var(--line);border-radius:6px;padding:12px 16px;min-width:88px;text-align:center">
                    <div style="font-family:var(--display);font-size:26px;line-height:1">{{ $lfc->goal_difference > 0 ? '+' : '' }}{{ $lfc->goal_difference }}</div>
                    <div style="font-size:10px;font-weight:800;letter-spacing:1.2px;text-transform:uppercase;color:var(--muted);margin-top:4px">Разница</div>
                </div>
            </div>
        @endif

        <div class="tablecard">
            <table class="lg">
                <thead>
                    <tr>
                        <th>#</th>
                        <th class="team">Команда</th>
                        <th>И</th>
                        <th class="hide">В</th>
                        <th class="hide">Н</th>
                        <th class="hide">П</th>
                        <th class="hide">Мячи</th>
                        <th>Р</th>
                        <th>О</th>
                        <th class="hide">Форма</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($rows as $r)
                    <tr class="{{ $r->isLiverpool() ? 'lfc' : '' }}">
                        <td class="pos">
                            <span class="zone {{ $r->zone() }}"></span>{{ $r->position }}
                        </td>
                        <td class="team">
                            @if($r->team_crest)
                                <img class="crest" src="{{ $r->team_crest }}" alt="" loading="lazy">
                            @endif
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

        <div class="legend">
            <span><i style="background:#1a56b8"></i> Лига чемпионов</span>
            <span><i style="background:#f5b22d"></i> Лига Европы</span>
            <span><i style="background:#c8102e"></i> Вылет</span>
        </div>

        {{-- График движения «Ливерпуля» по турам. Рисуется только если
             накоплено больше одного снимка. --}}
        @if(count($history) > 1)
            @php
                $w = 860; $h = 240; $padL = 34; $padR = 14; $padT = 16; $padB = 26;
                $mds = array_column($history, 'matchday');
                $minMd = min($mds); $maxMd = max($mds);
                $spanMd = max(1, $maxMd - $minMd);
                $innerW = $w - $padL - $padR;
                $innerH = $h - $padT - $padB;
                $pts = [];
                foreach ($history as $p) {
                    $x = $padL + ($p['matchday'] - $minMd) / $spanMd * $innerW;
                    // Позиция 1 — сверху, 20 — снизу.
                    $y = $padT + (($p['position'] - 1) / 19) * $innerH;
                    $pts[] = ['x' => round($x, 1), 'y' => round($y, 1), 'p' => $p];
                }
                $poly = implode(' ', array_map(fn ($q) => "{$q['x']},{$q['y']}", $pts));
            @endphp

            <div class="chartcard">
                <h3>Движение по турам</h3>
                <div class="sub">Место «Ливерпуля» в таблице, туры {{ $minMd }}–{{ $maxMd }}. История накапливается при каждой синхронизации.</div>

                <svg viewBox="0 0 {{ $w }} {{ $h }}" style="width:100%;height:auto" role="img"
                     aria-label="График места «Ливерпуля» по турам">
                    {{-- Горизонтальные линии для мест 1, 5, 10, 15, 20 --}}
                    @foreach([1,5,10,15,20] as $line)
                        @php $ly = $padT + (($line - 1) / 19) * $innerH; @endphp
                        <line x1="{{ $padL }}" y1="{{ $ly }}" x2="{{ $w - $padR }}" y2="{{ $ly }}"
                              stroke="#e4ded4" stroke-width="1"/>
                        <text x="{{ $padL - 8 }}" y="{{ $ly + 4 }}" text-anchor="end"
                              font-size="10" font-weight="700" fill="#6f645d">{{ $line }}</text>
                    @endforeach

                    {{-- Зона Лиги чемпионов (1–4) --}}
                    @php $uclH = (3 / 19) * $innerH; @endphp
                    <rect x="{{ $padL }}" y="{{ $padT }}" width="{{ $innerW }}" height="{{ $uclH }}"
                          fill="#1a56b8" opacity="0.06"/>

                    <polyline points="{{ $poly }}" fill="none" stroke="#c8102e" stroke-width="2.5"
                              stroke-linejoin="round" stroke-linecap="round"/>

                    @foreach($pts as $q)
                        <circle cx="{{ $q['x'] }}" cy="{{ $q['y'] }}" r="3.5" fill="#c8102e"/>
                        <title>Тур {{ $q['p']['matchday'] }}: {{ $q['p']['position'] }}-е место, {{ $q['p']['points'] }} очк.</title>
                    @endforeach

                    <text x="{{ $padL }}" y="{{ $h - 6 }}" font-size="10" font-weight="700" fill="#6f645d">тур {{ $minMd }}</text>
                    <text x="{{ $w - $padR }}" y="{{ $h - 6 }}" text-anchor="end" font-size="10" font-weight="700" fill="#6f645d">тур {{ $maxMd }}</text>
                </svg>
            </div>
        @elseif($lfc)
            <div class="chartcard">
                <h3>Движение по турам</h3>
                <div class="sub">
                    Пока сохранён только один снимок таблицы (тур {{ $matchday }}). График появится,
                    когда синхронизация выполнится в нескольких турах — история накапливается автоматически.
                </div>
            </div>
        @endif
    @endif
</div>

@include('public.partials.footer')
</body>
</html>
