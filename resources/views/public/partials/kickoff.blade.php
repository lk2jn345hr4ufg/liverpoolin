{{-- Время матча: по месту проведения + у посетителя.
     Параметры: $f (Fixture), $compact (bool, необязательно — в одну строку). --}}
@once
<style>
    .ko{display:inline-flex;flex-direction:column;gap:2px;line-height:1.25;text-align:inherit}
    .ko.compact{flex-direction:row;flex-wrap:wrap;gap:4px 10px}
    .ko-venue{font-weight:800;font-size:13px;color:var(--ink);white-space:nowrap}
    .ko-venue small,.ko-user small{font-weight:700;font-size:10px;letter-spacing:.4px;text-transform:uppercase;color:var(--muted)}
    .ko-user{font-weight:700;font-size:12px;color:var(--red);white-space:nowrap}
    .ko-user[hidden]{display:none}
</style>
@endonce

@if($f->kickoff_at)
    @php
        $vk  = $f->venueKickoff();
        $vtz = $f->venueTimezone();
    @endphp
    <span class="ko {{ ! empty($compact) ? 'compact' : '' }}">
        <span class="ko-venue" title="Время по месту проведения">🏟 {{ $vk->format('H:i') }} <small>{{ $f->venueCityLabel() }}</small></span>
        <span class="ko-user"
              data-user-time="{{ $f->kickoff_at->copy()->utc()->toIso8601String() }}"
              data-venue-tz="{{ $vtz }}"
              data-venue-date="{{ $vk->toDateString() }}"
              title="Время в вашем часовом поясе">🕑 <span class="ko-t">{{ $f->kickoff_at->copy()->utc()->format('H:i') }}</span> <small>у вас</small></span>
    </span>
@else
    <span class="ko"><span class="ko-venue">Время уточняется</span></span>
@endif
