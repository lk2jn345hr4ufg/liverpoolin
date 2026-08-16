@extends('layouts.admin')
@section('title', 'API-Football')

@section('content')
    <h1>API-Football <span class="muted" style="font-size:14px;font-weight:400">(api-sports.io)</span></h1>

    {{-- Состояние подключения --}}
    @if(! $configured)
        <div class="card" style="border-left:4px solid #b8901f">
            <h3>Ключ не подключён</h3>
            <p class="muted">Зарегистрируйтесь на dashboard.api-football.com/register — карта не нужна,
            бесплатный тариф остаётся бесплатным. Ключ придёт сразу после регистрации.</p>
        </div>
    @elseif($error)
        <div class="card" style="border-left:4px solid #c8102e">
            <h3>Ошибка подключения</h3>
            <p class="muted">{{ $error }}</p>
        </div>
    @elseif($status)
        <div class="grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:16px">
            <div class="stat">
                <div class="n" style="font-size:20px">{{ $status['plan'] ?: '—' }}</div>
                <div class="l">Тариф</div>
            </div>
            <div class="stat">
                <div class="n">{{ $status['used'] }}</div>
                <div class="l">Использовано</div>
            </div>
            <div class="stat">
                <div class="n" style="color:{{ $status['left'] < 10 ? '#c8102e' : '#166534' }}">{{ $status['left'] }}</div>
                <div class="l">Осталось сегодня</div>
            </div>
            <div class="stat">
                <div class="n">{{ $status['limit'] }}</div>
                <div class="l">Лимит в сутки</div>
            </div>
        </div>

        <div class="card" style="border-left:4px solid #166534">
            <h3>Подключение работает</h3>
            <p class="muted">
                Аккаунт: {{ $status['account'] ?: '—' }}
                @if($status['end']) · подписка до {{ $status['end'] }} @endif
                @if($teamName) · команда по текущему ID: <strong>{{ $teamName }}</strong> @endif
            </p>
        </div>
    @endif

    {{-- Настройки --}}
    <div class="card">
        <h3>Настройки</h3>
        <form method="post" action="{{ route('admin.apifootball.update') }}">
            @csrf @method('PUT')

            <label>Ключ API</label>
            <input type="password" name="apifootball_key" autocomplete="off"
                   placeholder="{{ $configured ? '•••••••• (ключ сохранён)' : 'Вставьте ключ из личного кабинета' }}">
            <p class="muted">Хранится в базе в зашифрованном виде. Оставьте пустым, чтобы не менять.</p>

            <label style="margin-top:12px">ID команды</label>
            <input name="apifootball_team_id" value="{{ $teamId }}">
            <p class="muted">
                <strong>40</strong> — «Ливерпуль» в API-Football.
                Обратите внимание: в football-data.org у «Ливерпуля» другой ID — 64.
                Это разные системы нумерации, не путайте их.
            </p>

            <div style="margin-top:12px">
                <button class="btn primary">Сохранить</button>
            </div>
        </form>

        @if($configured)
            <form method="post" action="{{ route('admin.apifootball.test') }}" style="margin-top:10px">
                @csrf
                <button class="btn ghost">Проверить подключение</button>
                <span class="muted">— расходует 2 запроса из суточной квоты</span>
            </form>
        @endif
    </div>

    {{-- Что даёт этот API --}}
    <div class="card">
        <h3>Какие данные доступны</h3>
        <table>
            <thead><tr><th>Эндпоинт</th><th>Что даёт</th><th>Нужно нам</th></tr></thead>
            <tbody>
                <tr>
                    <td><code>/transfers</code></td>
                    <td>Трансферы: игрок, из клуба, в клуб, дата, тип (аренда / выкуп / свободный агент), сумма</td>
                    <td><span class="badge published">Основное</span></td>
                </tr>
                <tr>
                    <td><code>/injuries</code></td>
                    <td>Травмы и дисквалификации по игрокам и матчам</td>
                    <td><span class="badge edited">Полезно</span></td>
                </tr>
                <tr>
                    <td><code>/players</code></td>
                    <td>Профили и статистика игроков за сезон</td>
                    <td><span class="badge edited">Полезно</span></td>
                </tr>
                <tr>
                    <td><code>/players/squads</code></td>
                    <td>Текущий состав команды</td>
                    <td><span class="badge edited">Полезно</span></td>
                </tr>
                <tr>
                    <td><code>/fixtures</code>, <code>/standings</code></td>
                    <td>Матчи и турнирная таблица</td>
                    <td><span class="badge scraped">Уже есть</span></td>
                </tr>
            </tbody>
        </table>
        <p class="muted" style="margin-top:10px">
            Матчи и таблицу мы уже берём из football-data.org, дублировать их здесь не нужно —
            это только тратило бы суточную квоту.
        </p>
    </div>

    {{-- Ограничения --}}
    <div class="card">
        <h3>Ограничения бесплатного тарифа</h3>
        <ul style="margin:0 0 0 18px;font-size:14px;line-height:1.7">
            <li><strong>100 запросов в сутки.</strong> Лимит суточный, без «накопления»: если исчерпать его днём, до следующих суток запросы не пройдут.</li>
            <li><strong>Доступны все эндпоинты</strong> — те же, что и на платных тарифах.</li>
            <li><strong>Ограничен набор сезонов.</strong> Глубокий архив прошлых лет закрыт — как и у football-data.org.</li>
            <li><strong>Код 429</strong> означает исчерпанную квоту, а не ошибку в коде.</li>
        </ul>
        <p class="muted" style="margin-top:10px">
            Для одной команды этого с запасом: синхронизация трансферов раз в сутки — это единицы запросов.
            Чтобы не жечь квоту, данные стоит кэшировать в базе, а не запрашивать при каждом открытии страницы.
        </p>
    </div>

    {{-- Ссылки --}}
    <div class="card">
        <h3>Ссылки</h3>
        <ul style="margin:0 0 0 18px;font-size:14px;line-height:1.8">
            <li>Регистрация ключа — dashboard.api-football.com/register</li>
            <li>Документация v3 — api-football.com/documentation-v3</li>
            <li>Тарифы — api-football.com/pricing</li>
        </ul>
    </div>
@endsection
