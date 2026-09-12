<!DOCTYPE html>
<html lang="ru">
<head>
    @include('public.partials.head', [
        'title'       => $post->title . ' — LiverpoolIn',
        'description' => $post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($post->body), 160),
        'image'       => $post->image_url,
        'canonical'   => route('posts.show', $post),
        'ogType'      => 'article',
    ])
    <style>
        body{line-height:1.6}
        .narrow{max-width:760px;margin:0 auto;padding:0 20px}
        article{background:var(--card);border:1px solid var(--line);border-radius:6px;margin:26px auto;overflow:hidden}
        .hero{position:relative;background:linear-gradient(140deg,var(--red),var(--wine))}
        .hero img{height:380px}
        .abody{padding:28px 34px 36px}
        h1{font-family:var(--display);font-weight:400;text-transform:uppercase;line-height:1;letter-spacing:.5px;font-size:clamp(26px,4.4vw,44px);margin:12px 0 12px;text-wrap:balance}
        .meta{font-size:12px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:var(--muted);padding-bottom:18px;border-bottom:1px solid var(--line);margin-bottom:22px}

        /* Оформление HTML из редактора */
        .prose{font-size:17px}
        .prose p{margin:0 0 18px}
        .prose h1,.prose h2{font-family:var(--display);font-weight:400;text-transform:uppercase;letter-spacing:.5px;margin:26px 0 12px;font-size:26px}
        .prose h3{font-weight:800;margin:22px 0 10px;font-size:20px}
        .prose ul,.prose ol{margin:0 0 18px 22px}
        .prose li{margin-bottom:6px}
        .prose a{color:var(--red);font-weight:600;text-decoration:underline}
        .prose blockquote{border-left:4px solid var(--gold);background:#fbf7ee;margin:0 0 18px;padding:12px 18px;color:#5c5148;font-style:italic}
        .prose img{max-width:100%;height:auto;border-radius:6px;margin:12px 0}
        .prose pre{background:#1a1413;color:#f6f3ee;padding:14px 16px;border-radius:6px;overflow:auto;font-size:14px;margin:0 0 18px}
        .prose strong{font-weight:800}

        .back{display:inline-block;margin:0 0 40px;font-weight:800;font-size:13px;letter-spacing:1px;text-transform:uppercase;color:var(--red)}
    </style>

    <script type="application/ld+json">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@type'    => 'Article',
        'headline' => $post->title,
        'datePublished' => optional($post->published_at)->toAtomString(),
        'dateModified'  => optional($post->updated_at)->toAtomString(),
        'image'    => $post->image_url ? [$post->image_url] : [],
        'author'   => ['@type' => 'Organization', 'name' => $post->author ?: 'LiverpoolIn'],
        'publisher'=> ['@type' => 'Organization', 'name' => 'LiverpoolIn'],
        'mainEntityOfPage' => route('posts.show', $post),
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
    </script>
</head>
<body>

@include('public.partials.header', ['activeCat' => $post->category?->slug])

<div class="narrow">
    <article>
        @if($post->image_url)<div class="hero"><img src="{{ $post->image_url }}" alt="{{ $post->title }}"></div>@endif
        <div class="abody">
            @if($post->category)
                <a href="{{ route('category', $post->category->slug) }}">
                    <span class="tag" style="background:{{ $post->category->color }}">{{ $post->category->name }}</span>
                </a>
            @else
                <span class="tag" style="background:var(--red)">Статья</span>
            @endif

            <h1>{{ $post->title }}</h1>
            <div class="meta">
                {{ $post->published_at?->isoFormat('D MMMM YYYY') }}
                @if($post->author) · {{ $post->author }} @endif
            </div>

            {{-- Тело статьи — доверенный HTML из админ-редактора --}}
            <div class="prose">
                {!! $post->body !!}
            </div>
        </div>
    </article>

    <a class="back" href="{{ route('posts.index') }}">← Ко всем статьям</a>
</div>

@include('public.partials.footer')
</body>
</html>
