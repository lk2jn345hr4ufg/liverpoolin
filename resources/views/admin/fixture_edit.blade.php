@extends('layouts.admin')
@section('title', $fixture->exists ? 'Edit fixture' : 'Add fixture')

@section('content')
    <a class="btn ghost" href="{{ route('admin.fixtures') }}">← Back to fixtures</a>
    <h1>{{ $fixture->exists ? 'Edit fixture' : 'Add fixture' }}</h1>

    @if($errors->any())
        <div class="flash err">{{ $errors->first() }}</div>
    @endif

    <form method="post"
          action="{{ $fixture->exists ? route('admin.fixtures.update', $fixture) : route('admin.fixtures.store') }}">
        @csrf
        @if($fixture->exists) @method('PUT') @endif

        <div class="card">
            <div class="row">
                <div>
                    <label>Home team</label>
                    <input name="home_team" value="{{ old('home_team', $fixture->home_team) }}" required>
                </div>
                <div>
                    <label>Away team</label>
                    <input name="away_team" value="{{ old('away_team', $fixture->away_team) }}" required>
                </div>
            </div>

            <div class="row" style="margin-top:12px">
                <div>
                    <label>Competition</label>
                    <input name="competition" value="{{ old('competition', $fixture->competition) }}">
                </div>
                <div>
                    <label>Venue</label>
                    <input name="venue" value="{{ old('venue', $fixture->venue) }}">
                </div>
            </div>

            <div class="row" style="margin-top:12px">
                <div>
                    <label>Kick-off</label>
                    <input type="datetime-local" name="kickoff_at"
                           value="{{ old('kickoff_at', $fixture->kickoff_at?->format('Y-m-d\TH:i')) }}">
                </div>
                <div>
                    <label>Status</label>
                    <select name="status">
                        @foreach(['scheduled','live','finished','postponed'] as $s)
                            <option value="{{ $s }}" @selected(old('status',$fixture->status)===$s)>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row" style="margin-top:12px">
                <div>
                    <label>Home score</label>
                    <input type="number" name="home_score" min="0" max="99"
                           value="{{ old('home_score', $fixture->home_score) }}" placeholder="—">
                </div>
                <div>
                    <label>Away score</label>
                    <input type="number" name="away_score" min="0" max="99"
                           value="{{ old('away_score', $fixture->away_score) }}" placeholder="—">
                </div>
            </div>
        </div>

        <button class="btn primary">{{ $fixture->exists ? 'Save changes' : 'Add fixture' }}</button>
    </form>
@endsection
