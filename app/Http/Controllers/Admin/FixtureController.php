<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Fixture;
use Illuminate\Http\Request;

class FixtureController extends Controller
{
    public function index()
    {
        $upcoming = Fixture::where('kickoff_at', '>=', now()->startOfDay())
                           ->orderBy('kickoff_at')->get();

        $past = Fixture::where('kickoff_at', '<', now()->startOfDay())
                       ->orderByDesc('kickoff_at')->limit(40)->get();

        return view('admin.fixtures', compact('upcoming', 'past'));
    }

    public function create()
    {
        return view('admin.fixture_edit', ['fixture' => new Fixture()]);
    }

    public function edit(Fixture $fixture)
    {
        return view('admin.fixture_edit', compact('fixture'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['fingerprint'] = Fixture::makeFingerprint(
            $data['home_team'], $data['away_team'], $data['kickoff_at'] ?? null
        );
        $data['source'] = 'manual';

        Fixture::create($data);

        return redirect()->route('admin.fixtures')->with('ok', 'Матч добавлен.');
    }

    public function update(Request $request, Fixture $fixture)
    {
        $fixture->update($this->validated($request));

        return redirect()->route('admin.fixtures')->with('ok', 'Матч обновлён.');
    }

    public function destroy(Fixture $fixture)
    {
        $fixture->delete();

        return back()->with('ok', 'Матч удалён.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'competition' => 'nullable|string|max:120',
            'home_team'   => 'required|string|max:120',
            'away_team'   => 'required|string|max:120',
            'venue'       => 'nullable|string|max:120',
            'venue_tz'    => 'nullable|timezone',
            'kickoff_at'  => 'nullable|date',
            'home_score'  => 'nullable|integer|min:0|max:99',
            'away_score'  => 'nullable|integer|min:0|max:99',
            'status'      => 'required|in:scheduled,live,finished,postponed',
        ]);

        /*
         | Поле «Начало» в форме вводится по времени МЕСТА ПРОВЕДЕНИЯ.
         | Переводим его в UTC для хранения, используя пояс стадиона
         | (ручной или определённый автоматически).
         */
        if (! empty($data['kickoff_at'])) {
            $probe = new Fixture($data);
            $tz = $probe->venueTimezone() ?? 'UTC';

            $data['kickoff_at'] = \Illuminate\Support\Carbon::parse($data['kickoff_at'], $tz)->utc();
        }

        $data['venue_tz'] = $data['venue_tz'] ?: null;

        return $data;
    }
}
