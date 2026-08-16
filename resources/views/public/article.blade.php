<!DOCTYPE html>
<html lang="ru">
<head>
    @include('public.partials.head', ['title' => $article->displayTitle() . ' — LiverpoolIn'])
    <style>
        body{line-height:1.6}
        .narrow{max-width:760px;margin:0 auto;padding:0 20px}
        article{background:var(--card);border:1px solid var(--line);border-radius:6px;margin:26px auto;overflow:hidden}
        .hero{position:relative;background:linear-gradient(140deg,var(--red),var(--wine))}
        .hero img{height:360px}
        .abody{padding:28px 32px 34px}
        h1{font-family:var(--display);font-weight:400;text-transform:uppercase;line-height:1;letter-spacing:.5px;font-size:clamp(26px,4.4vw,42px);margin:12px 0 12px;text-wrap:balance}
        .meta{font-size:12px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:var(--muted);padding-bottom:18px;border-bottom:1px solid var(--line);margin-bottom:22px}
        .content p{margin:0 0 18px;font-size:17px}
        .source{margin-top:26px;padding-top:18px;border-top:1px solid var(--line);font-size:13px;color:var(--muted)}
        .source a{color:var(--red);font-weight:700}
        .back{display:inline-block;margin:0 0 40px;font-weight:800;font-size:13px;letter-spacing:1px;text-transform:uppercase;color:var(--red)}
    </style>
</head>
<body>

@include('public.partials.header', ['activeCat' => $article->category?->slug])

<div class="narrow">
    <article>
        @if($article->image_url)<div class="hero"><img src="{{ $article->image_url }}" alt=""></div>@endif
        <div class="abody">
            @if($article->category)
                <a href="{{ route('category', $article->category->slug) }}">
                    <span class="tag" style="background:{{ $article->category->color }}">{{ $article->category->name }}</span>
                </a>
            @else
                <span class="tag" style="background:var(--red)">{{ $article->source_name ?: 'Новости' }}</span>
            @endif

            <h1>{{ $article->displayTitle() }}</h1>
            <div class="meta">{{ $article->published_at?->isoFormat('D MMMM YYYY, HH:mm') }} · отредактировано ИИ</div>

            <div class="content">
                @foreach(preg_split('/\n+/', trim($article->edited_content)) as $para)
                    @if(trim($para) !== '')<p>{{ $para }}</p>@endif
                @endforeach
            </div>

            <div class="source">
                По материалам {{ $article->source_name }}.
                <a href="{{ $article->source_url }}" rel="nofollow noopener" target="_blank">Читать оригинал ↗</a>
            </div>
        </div>
    </article>

    <a class="back" href="{{ route('home') }}">← Ко всем новостям</a>
</div>

@include('public.partials.footer')
</body>
</html>
