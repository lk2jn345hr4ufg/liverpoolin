@extends('layouts.admin')
@section('title', 'Articles')

@section('content')
    <div class="topbar">
        <h1>Статьи</h1>
        <form class="inline" method="post" action="{{ route('admin.run.scrapeNews') }}">@csrf
            <button class="btn ghost">⬇ Собрать новости</button>
        </form>
        <form class="inline" method="post" action="{{ route('admin.run.editPending') }}">@csrf
            <button class="btn primary">✨ Обработать ИИ</button>
        </form>
    </div>

    <div class="card">
        <div style="margin-bottom:10px">
            @foreach(['all'=>'Все','scraped'=>'Черновики','edited'=>'Обработаны','published'=>'Опубликованы','failed'=>'Ошибки'] as $k=>$label)
                <a class="btn {{ $status===$k?'primary':'ghost' }}"
                   href="{{ route('admin.articles', ['status'=>$k, 'category'=>$categoryId]) }}">{{ $label }}</a>
            @endforeach
        </div>
        <div>
            <a class="btn {{ empty($categoryId)?'primary':'ghost' }}"
               href="{{ route('admin.articles', ['status'=>$status]) }}">Все категории</a>
            @foreach($categories as $c)
                <a class="btn {{ (string)$categoryId === (string)$c->id ?'primary':'ghost' }}"
                   href="{{ route('admin.articles', ['status'=>$status,'category'=>$c->id]) }}">{{ $c->name }}</a>
            @endforeach
        </div>
    </div>

    <div class="card">
        <table>
            <thead><tr><th>Заголовок</th><th>Категория</th><th>Статус</th><th>Действия</th></tr></thead>
            <tbody>
            @forelse($articles as $a)
                <tr>
                    <td>{{ \Illuminate\Support\Str::limit($a->displayTitle() ?: $a->original_title, 50) }}</td>
                    <td>
                        @if($a->category)
                            <span class="badge" style="background:{{ $a->category->color }};color:#fff">{{ $a->category->name }}</span>
                        @else
                            <span class="muted">—</span>
                        @endif
                    </td>
                    <td><span class="badge {{ $a->status }}">{{ $a->status }}</span></td>
                    <td>
                        <a class="btn ghost" href="{{ route('admin.articles.edit', $a) }}">Открыть</a>
                        @if($a->status !== 'published' && $a->canBePublished())
                            <form class="inline" method="post" action="{{ route('admin.articles.publish', $a) }}">
                                @csrf<button class="btn primary">Опубликовать</button>
                            </form>
                        @elseif($a->status === 'published')
                            <form class="inline" method="post" action="{{ route('admin.articles.unpublish', $a) }}">
                                @csrf<button class="btn ghost">Снять</button>
                            </form>
                        @endif
                        <form class="inline" method="post" action="{{ route('admin.articles.destroy', $a) }}"
                              onsubmit="return confirm('Удалить статью?')">
                            @csrf @method('DELETE')<button class="btn danger">Удалить</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="muted">Пусто. Нажмите «Собрать новости».</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $articles->links() }}
@endsection
