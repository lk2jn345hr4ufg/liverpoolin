@extends('layouts.admin')
@section('title', 'Review article')

@section('content')
    <a class="btn ghost" href="{{ route('admin.articles') }}">← Назад</a>
    <h1>Проверка и редактирование <span class="badge {{ $article->status }}">{{ $article->status }}</span></h1>

    <div class="card">
        <h3>Оригинал (не публикуется как есть)</h3>
        <strong>{{ $article->original_title }}</strong>
        <p class="muted">{{ \Illuminate\Support\Str::limit($article->original_content, 400) }}</p>
        <a href="{{ $article->source_url }}" target="_blank" rel="nofollow noopener">Источник ↗</a>

        <form method="post" action="{{ route('admin.articles.aiEdit', $article) }}" style="margin-top:12px">
            @csrf
            <button class="btn primary">✨ {{ $article->edited_content ? 'Переписать заново' : 'Обработать' }} через Gemini</button>
        </form>

        @if($article->edit_error)
            <p class="flash err" style="margin-top:10px">Ошибка: {{ $article->edit_error }}</p>
        @endif
    </div>

    <div class="card">
        <h3>Версия для публикации</h3>

        <form method="post" action="{{ route('admin.articles.update', $article) }}">
            @csrf @method('PUT')

            <label>Заголовок</label>
            <input name="edited_title" value="{{ old('edited_title', $article->edited_title) }}">

            <label style="margin-top:12px">Категория
                <span class="muted" style="font-weight:400">— выбрана ИИ, можно изменить</span>
            </label>
            <select name="category_id">
                <option value="">— без категории —</option>
                @foreach($categories as $c)
                    <option value="{{ $c->id }}" @selected((string) old('category_id', $article->category_id) === (string) $c->id)>
                        {{ $c->name }}
                    </option>
                @endforeach
            </select>

            <label style="margin-top:12px">Текст</label>
            <textarea name="edited_content">{{ old('edited_content', $article->edited_content) }}</textarea>

            <div style="margin-top:12px">
                <button class="btn ghost">Сохранить</button>
            </div>
        </form>

        <div style="margin-top:12px">
            @if($article->canBePublished())
                <form method="post" action="{{ route('admin.articles.publish', $article) }}">
                    @csrf
                    <button class="btn primary">Опубликовать</button>
                </form>
            @else
                <span class="muted">Обработайте статью через ИИ, чтобы её можно было опубликовать.</span>
            @endif
        </div>
    </div>
@endsection
