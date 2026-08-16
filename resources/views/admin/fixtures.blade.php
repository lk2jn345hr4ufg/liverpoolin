@extends('layouts.admin')
@section('title', 'Fixtures')

@section('content')
    <div class="topbar">
        <h1>Fixtures</h1>
        <a class="btn primary" href="{{ route('admin.fixtures.create') }}">+ Add fixture</a>
    </div>

    <div class="card">
        <h3>Upcoming</h3>
        <table>
            <thead><tr><th>Date</th><th>Match</th><th>Comp</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse($upcoming as $f)
                <tr>
                    <td>{{ $f->kickoff_at?->format('D d M · H:i') ?? 'TBC' }}</td>
                    <td><strong>{{ $f->home_team }}</strong> vs <strong>{{ $f->away_team }}</strong></td>
                    <td>{{ $f->competition ?? '—' }}</td>
                    <td><span class="badge {{ $f->status }}">{{ $f->status }}</span></td>
                    <td>
                        <a class="btn ghost" href="{{ route('admin.fixtures.edit', $f) }}">Edit</a>
                        <form class="inline" method="post" action="{{ route('admin.fixtures.destroy', $f) }}"
                              onsubmit="return confirm('Delete this fixture?')">
                            @csrf @method('DELETE')<button class="btn danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="muted">No upcoming fixtures. Scrape them from the Dashboard or add one.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="card">
        <h3>Past &amp; results</h3>
        <table>
            <thead><tr><th>Date</th><th>Result</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse($past as $f)
                <tr>
                    <td>{{ $f->kickoff_at?->format('d M Y') }}</td>
                    <td>
                        {{ $f->home_team }}
                        <strong>{{ $f->home_score !== null ? "{$f->home_score}–{$f->away_score}" : 'vs' }}</strong>
                        {{ $f->away_team }}
                    </td>
                    <td><span class="badge {{ $f->status }}">{{ $f->status }}</span></td>
                    <td><a class="btn ghost" href="{{ route('admin.fixtures.edit', $f) }}">Edit</a></td>
                </tr>
            @empty
                <tr><td colspan="4" class="muted">No past fixtures.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
