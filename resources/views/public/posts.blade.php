<!DOCTYPE html>
<html lang="ru">
<head>
    @include('public.partials.head', ['title' => 'Статьи — LiverpoolIn', 'description' => 'Авторские статьи, обзоры и аналитика о «Ливерпуле».'])
    <style>
        .page{max-width:1120px;margin:0 auto;padding:0 20px}
        .pagehead{padding:30px 0 6px}
        .pagehead h1{font-family:var(--display);font-weight:400;text-transform:uppercase;
                     font-size:clamp(28px,4.4vw,44px);letter-spacing:1px;line-height:1}
        .pagehead .intro{color:var(--muted);font-size:15px;margin-top:8px}

        .grid{display:grid;grid-template-columns:1fr 1fr 1fr;gap:22px;margin-top:22px}
        @media(max-width:820px){.grid{grid-template-columns:1fr 1fr}}
        @media(max-width:560px){.grid{grid-template-columns:1fr}}

        .pager{display:flex;gap:6px;flex-wrap:wrap;margin:30px 0 50px;align-items:center;justify-content:center}
        .pager a,.pager span{font-size:14px;font-weight:700;padding:8px 13px;border:1px solid var(--line);
             border-radius:6px;background:var(--card);color:var(--ink);text-decoration:none}
        .pager a:hover{border-color:var(--red);color:var(--red)}
        .pager .cur{background:var(--red);border-color:var(--red);color:#fff}
        .pager svg{display:none}
    </style>
</head>
<body>

@include('public.partials.header', ['activeCat' => null])

<div class="page">
    <div class="pagehead">
        <h1>Статьи</h1>
        <p class="intro">Авторские обзоры, аналитика и колонки о «Ливерпуле».</p>
    </div>

    @if($posts->isNotEmpty())
        <div class="grid">
            @foreach($posts as $p)
                <a class="story" href="{{ route('posts.show', $p) }}">
                    <div class="thumb">
                        @if($p->image_url)<img src="{{ $p->image_url }}" alt="{{ $p->title }}">@else<span class="mark">LFC</span>@endif
                        @if($p->category)
                            <span class="tag" style="background:{{ $p->category->color }}">{{ $p->category->name }}</span>
                        @endif
                    </div>
                    <div class="body">
                        <span class="kicker">Статья</span>
                        <h3>{{ $p->title }}</h3>
                        <p class="ex">{{ \Illuminate\Support\Str::limit($p->excerpt ?: strip_tags($p->body), 120) }}</p>
                        <span class="time">{{ $p->published_at?->isoFormat('D MMM YYYY') }}</span>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="pager">{{ $posts->links() }}</div>
    @else
        <div class="empty" style="margin-top:26px">
            Статьи скоро появятся. Загляните позже.
        </div>
    @endif
</div>

@include('public.partials.footer')
</body>
</html>
