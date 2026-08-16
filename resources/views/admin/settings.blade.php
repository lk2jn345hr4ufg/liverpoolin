@extends('layouts.admin')
@section('title', 'Настройки')

@section('content')
    <h1>Настройки</h1>

    <form method="post" action="{{ route('admin.settings.update') }}">
        @csrf @method('PUT')

        <div class="card">
            <h3>Gemini — ключ API</h3>
            <p class="muted">
                Хранится в базе в зашифрованном виде.
                @if($hasKey) Ключ уже сохранён — оставьте поле пустым, чтобы не менять его. @endif
                Получить: aistudio.google.com/apikey
            </p>
            <input type="password" name="gemini_api_key" autocomplete="off"
                   placeholder="{{ $hasKey ? '•••••••• (ключ сохранён)' : 'Вставьте ключ Gemini API' }}">

            <label style="margin-top:12px">Модель</label>
            <input name="gemini_model" value="{{ old('gemini_model', $geminiModel) }}">
            <p class="muted">Актуальные варианты: <code>gemini-3.5-flash-lite</code> (дешевле), <code>gemini-3.6-flash</code>.</p>
        </div>

        <div class="card">
            <h3>football-data.org — расписание матчей</h3>
            <p class="muted">
                Бесплатный ключ: football-data.org/client/register.
                @if($hasFdKey) Ключ уже сохранён — оставьте пустым, чтобы не менять. @endif
                Лимит бесплатного тарифа — около 10 запросов в минуту; синхронизация делает 1 запрос.
            </p>
            <input type="password" name="football_data_key" autocomplete="off"
                   placeholder="{{ $hasFdKey ? '•••••••• (ключ сохранён)' : 'Вставьте ключ football-data.org' }}">

            <label style="margin-top:12px">ID команды</label>
            <input name="football_data_team_id" value="{{ old('football_data_team_id', $fdTeamId) }}">
            <p class="muted">64 — «Ливерпуль». Менять нужно, только если хотите другую команду.</p>
        </div>

        <div class="card">
            <h3>Промпт для редактуры</h3>
            <p class="muted">Добавляется к каждой статье перед отправкой в Gemini.
            Требуйте строгий JSON <code>{"title","content"}</code>. Категорию система запрашивает отдельно — её трогать не нужно.</p>
            <textarea name="edit_prompt" style="min-height:280px">{{ old('edit_prompt', $prompt) }}</textarea>
        </div>

        <button class="btn primary">Сохранить настройки</button>
    </form>
@endsection
