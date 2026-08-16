<!DOCTYPE html>
<html lang="ru">
<head>
    @include('public.partials.head', ['title' => $category->name . ' — LiverpoolIn'])
    <style>
        .pagehead{padding:28px 0 4px}
        .pagehead h1{font-family:var(--display);font-weight:400;text-transform:uppercase;font-size:clamp(28px,4.4vw,42px);letter-spacing:1px}
        .pagehead .desc{color:var(--muted);font-size:15px;max-width:640px;margin-top:8px}
        .pager{display:flex;gap:8px;margin:26px 0 10px}
        .pager a,.pager span{font-size:13px;font-weight:700;padding:7px 12px;border:1px solid var(--line);border-radius:4px;background:var(--card)}
    </style>
</head>
<body>

@include('public.partials.header', ['activeCat' => $category->slug])

<main class="wrap">
    <div class="pagehead">
        <span class="tag" style="background:{{ $category->color }}">{{ $category->name }}</span>
        <h1>{{ $category->name }}</h1>
        @if($category->description)<p class="desc">{{ $category->description }}</p>@endif
    </div>

    <div class="sec-head" style="margin-top:22px"><h2>Материалы</h2><span class="rule"></span></div>

    @if($articles->isNotEmpty())
        <div class="grid">
            @foreach($articles as $a)
                <a class="story" href="{{ route('article.show', $a) }}">
                    <div class="thumb">
                        @if($a->image_url)<img src="{{ $a->image_url }}" alt="">@else<span class="mark">LFC</span>@endif
                    </div>
                    <div class="body">
                        <span class="kicker">{{ $a->source_name ?: 'Новости' }}</span>
                        <h3>{{ $a->displayTitle() }}</h3>
                        <p class="ex">{{ \Illuminate\Support\Str::limit(strip_tags($a->edited_content), 110) }}</p>
                        <span class="time">{{ $a->published_at?->diffForHumans() }}</span>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="pager">{{ $articles->links() }}</div>
    @else
        <div class="empty">В этой категории пока нет материалов.</div>
    @endif
</main>

@include('public.partials.footer')
</body>
</html>
