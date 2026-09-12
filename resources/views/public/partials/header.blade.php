{{-- Шапка + панель категорий. Ожидает $categories и $activeCat (slug|null). --}}
<header class="masthead">
    <div class="bar">
        <a class="logo" href="{{ route('home') }}"><b>Liverpool</b><span>In</span></a>
        <nav class="nav">
            <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'on' : '' }}">Новости</a>
            <a href="{{ route('posts.index') }}" class="{{ request()->routeIs('posts.*') ? 'on' : '' }}">Статьи</a>
            <a href="{{ route('fixtures') }}" class="{{ request()->routeIs('fixtures') ? 'on' : '' }}">Расписание</a>
            <a href="{{ route('fixtures.international') }}" class="{{ request()->routeIs('fixtures.international') ? 'on' : '' }}">Еврокубки</a>
            <a href="{{ route('table') }}" class="{{ request()->routeIs('table') ? 'on' : '' }}">Таблица</a>
            <a href="{{ route('transfers') }}" class="{{ request()->routeIs('transfers') ? 'on' : '' }}">Трансферы</a>
        </nav>
    </div>
</header>

@if(!empty($categories) && count($categories))
    <div class="catbar">
        <div class="in">
            <a class="chip {{ empty($activeCat ?? null) && request()->routeIs('home') ? 'on' : '' }}"
               href="{{ route('home') }}">Все</a>
            @foreach($categories as $c)
                <a class="chip {{ ($activeCat ?? null) === $c->slug ? 'on' : '' }}"
                   href="{{ route('category', $c->slug) }}">{{ $c->name }}</a>
            @endforeach
        </div>
    </div>
@endif
