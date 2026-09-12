<!DOCTYPE html>
<html lang="ru">
<head>
    @include('public.partials.head', ['title' => 'Статьи — LiverpoolIn', 'description' => 'Все статьи и новости «Ливерпуля» — полный архив.'])
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
        .pager .dis{opacity:.4}
        .pager svg{display:none}
    </style>
</head>
<body>

@include('public.partials.header', ['activeCat' => $activeCat])

<div class="page">
    <div class="pagehead">
        <h1>Статьи</h1>
        <p class="intro">Полный архив материалов «Ливерпуля» — всего {{ $total }}.</p>
    </div>

    @if($articles->isNotEmpty())
        <div class="grid">
            @foreach($articles as $a)
                <a class="story" href="{{ route('article.show', $a) }}">
                    <div class="thumb">
                        @if($a->image_url)<img src="{{ $a->image_url }}" alt="{{ $a->displayTitle() }}">@else<span class="mark">LFC</span>@endif
                        @if($a->category)
                            <span class="tag" style="background:{{ $a->category->color }}">{{ $a->category->name }}</span>
                        @endif
                    </div>
                    <div class="body">
                        <span class="kicker">{{ $a->source_name ?: 'Новости' }}</span>
                        <h3>{{ $a->displayTitle() }}</h3>
                        <p class="ex">{{ \Illuminate\Support\Str::limit(strip_tags($a->edited_content), 110) }}</p>
                        <span class="time">{{ $a->published_at?->isoFormat('D MMM YYYY') }}</span>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="pager">
            {{ $articles->links() }}
        </div>
    @else
        <div class="empty" style="margin-top:26px">
            В этом разделе пока нет материалов.
        </div>
    @endif
</div>

@include('public.partials.footer')
</body>
</html>
