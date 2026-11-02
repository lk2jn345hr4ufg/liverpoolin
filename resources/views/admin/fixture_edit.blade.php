@extends('layouts.admin')
@section('title', $fixture->exists ? 'Редактирование матча' : 'Новый матч')

@section('content')
    <a class="btn ghost" href="{{ route('admin.fixtures') }}">← К матчам</a>
    <h1>{{ $fixture->exists ? 'Редактирование матча' : 'Новый матч' }}</h1>

    @if($errors->any())
        <div class="flash err">{{ $errors->first() }}</div>
    @endif

    @php
        // Время в форме показываем по месту проведения.
        $venueKick = $fixture->exists ? $fixture->venueKickoff() : null;
        $cities = config('venues.cities', []);
    @endphp

    <form method="post"
          action="{{ $fixture->exists ? route('admin.fixtures.update', $fixture) : route('admin.fixtures.store') }}">
        @csrf
        @if($fixture->exists) @method('PUT') @endif

        <div class="card">
            <div class="row">
                <div>
                    <label>Хозяева</label>
                    <input name="home_team" value="{{ old('home_team', $fixture->home_team) }}" required>
                </div>
                <div>
                    <label>Гости</label>
                    <input name="away_team" value="{{ old('away_team', $fixture->away_team) }}" required>
                </div>
            </div>

            <div class="row" style="margin-top:12px">
                <div>
                    <label>Турнир</label>
                    <input name="competition" value="{{ old('competition', $fixture->competition) }}">
                </div>
                <div>
                    <label>Стадион</label>
                    <input name="venue" value="{{ old('venue', $fixture->venue) }}">
                </div>
            </div>

            <div class="row" style="margin-top:12px">
                <div>
                    <label>Начало (по местному времени стадиона)</label>
                    <input type="datetime-local" name="kickoff_at"
                           value="{{ old('kickoff_at', $venueKick?->format('Y-m-d\TH:i')) }}">
                </div>
                <div>
                    <label>Часовой пояс стадиона</label>
                    <select name="venue_tz">
                        <option value="">Авто
                            @if($fixture->exists) ({{ $fixture->venueTimezone() ? ($cities[$fixture->venueTimezone()] ?? $fixture->venueTimezone()) : 'не определён → UTC' }}) @endif
                        </option>
                        @foreach($cities as $tz => $city)
                            <option value="{{ $tz }}" @selected(old('venue_tz', $fixture->venue_tz) === $tz)>{{ $city }} — {{ $tz }}</option>
                        @endforeach
                    </select>
                    <p class="muted" style="margin:4px 0 0">
                        Обычно определяется автоматически по турниру и клубу-хозяину.
                        Указывайте вручную для нейтральных стадионов (финалы, турне).
                    </p>
                </div>
            </div>

            <div class="row" style="margin-top:12px">
                <div>
                    <label>Статус</label>
                    <select name="status">
                        @foreach(['scheduled' => 'Запланирован', 'live' => 'Идёт', 'finished' => 'Завершён', 'postponed' => 'Перенесён'] as $s => $label)
                            <option value="{{ $s }}" @selected(old('status', $fixture->status ?: 'scheduled') === $s)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label>Счёт хозяев</label>
                    <input type="number" name="home_score" min="0" max="99"
                           value="{{ old('home_score', $fixture->home_score) }}" placeholder="—">
                </div>
                <div>
                    <label>Счёт гостей</label>
                    <input type="number" name="away_score" min="0" max="99"
                           value="{{ old('away_score', $fixture->away_score) }}" placeholder="—">
                </div>
            </div>
        </div>

        @if($fixture->exists && $fixture->source === 'football-data')
            <p class="muted">Этот матч синхронизируется с football-data.org: дата, счёт и статус
            перезапишутся при следующей синхронизации. Часовой пояс стадиона сохраняется.</p>
        @endif

        <button class="btn primary">{{ $fixture->exists ? 'Сохранить' : 'Добавить матч' }}</button>
    </form>
@endsection
