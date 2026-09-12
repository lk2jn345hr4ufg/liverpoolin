@extends('layouts.admin')
@section('title', 'Статьи')

@section('content')
    <div class="topbar">
        <h1>Статьи</h1>
        <a class="btn primary" href="{{ route('admin.posts.create') }}">+ Новая статья</a>
    </div>

    <div class="card">
        <table>
            <thead><tr><th>Заголовок</th><th>Категория</th><th>Статус</th><th>Дата</th><th>Действия</th></tr></thead>
            <tbody>
            @forelse($posts as $p)
                <tr>
                    <td>{{ \Illuminate\Support\Str::limit($p->title, 55) }}</td>
                    <td>
                        @if($p->category)
                            <span class="badge" style="background:{{ $p->category->color }};color:#fff">{{ $p->category->name }}</span>
                        @else
                            <span class="muted">—</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge {{ $p->status === 'published' ? 'published' : 'scraped' }}">
                            {{ $p->status === 'published' ? 'опубликована' : 'черновик' }}
                        </span>
                    </td>
                    <td class="muted">{{ $p->published_at?->isoFormat('D MMM YYYY') ?? $p->created_at->isoFormat('D MMM YYYY') }}</td>
                    <td>
                        <a class="btn ghost" href="{{ route('admin.posts.edit', $p) }}">Открыть</a>
                        @if($p->isPublished())
                            <a class="btn ghost" href="{{ route('posts.show', $p) }}" target="_blank">На сайте ↗</a>
                            <form class="inline" method="post" action="{{ route('admin.posts.unpublish', $p) }}">
                                @csrf<button class="btn ghost">Снять</button>
                            </form>
                        @else
                            <form class="inline" method="post" action="{{ route('admin.posts.publish', $p) }}">
                                @csrf<button class="btn primary">Опубликовать</button>
                            </form>
                        @endif
                        <form class="inline" method="post" action="{{ route('admin.posts.destroy', $p) }}"
                              onsubmit="return confirm('Удалить статью?')">
                            @csrf @method('DELETE')<button class="btn danger">Удалить</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="muted">Статей пока нет. Нажмите «Новая статья».</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{ $posts->links() }}
@endsection
