@extends('layouts.admin')
@section('title', $post->exists ? 'Редактирование статьи' : 'Новая статья')

@section('content')
    {{-- Trix — лёгкий HTML-редактор с CDN, без сборки на сервере --}}
    <link rel="stylesheet" href="https://unpkg.com/trix@2.1.1/dist/trix.css">
    <script src="https://unpkg.com/trix@2.1.1/dist/trix.umd.min.js"></script>
    <style>
        trix-editor{background:#fff;min-height:340px;border:1px solid #ccc;border-radius:0 0 6px 6px;font-size:15px;line-height:1.6}
        trix-toolbar .trix-button-group{border-color:#ccc}
        /* Скрываем кнопку вложений — загрузка файлов не настроена */
        trix-toolbar [data-trix-button-group="file-tools"]{display:none}
        .hint{font-size:12px;color:#888;margin-top:4px}
    </style>

    <a class="btn ghost" href="{{ route('admin.posts') }}">← К списку</a>
    <h1>{{ $post->exists ? 'Редактирование статьи' : 'Новая статья' }}
        @if($post->exists)
            <span class="badge {{ $post->status === 'published' ? 'published' : 'scraped' }}" style="font-size:11px;vertical-align:middle">
                {{ $post->status === 'published' ? 'опубликована' : 'черновик' }}
            </span>
        @endif
    </h1>

    @if($errors->any())
        <div class="flash err">{{ $errors->first() }}</div>
    @endif

    <form method="post" action="{{ $post->exists ? route('admin.posts.update', $post) : route('admin.posts.store') }}">
        @csrf
        @if($post->exists) @method('PUT') @endif

        <div class="card">
            <label>Заголовок</label>
            <input name="title" value="{{ old('title', $post->title) }}" required>
            @if($post->exists)
                <p class="hint">Адрес на сайте: <code>/articles/{{ $post->slug }}</code> (меняется при смене заголовка)</p>
            @endif

            <div class="row" style="margin-top:12px">
                <div>
                    <label>Категория</label>
                    <select name="category_id">
                        <option value="">— без категории —</option>
                        @foreach($categories as $c)
                            <option value="{{ $c->id }}" @selected((string)old('category_id', $post->category_id) === (string)$c->id)>
                                {{ $c->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label>Автор</label>
                    <input name="author" value="{{ old('author', $post->author) }}" placeholder="Редакция LiverpoolIn">
                </div>
            </div>

            <label style="margin-top:12px">Обложка (URL изображения)</label>
            <input name="image_url" value="{{ old('image_url', $post->image_url) }}" placeholder="https://...">

            <label style="margin-top:12px">Краткое описание (для карточек и SEO)</label>
            <textarea name="excerpt" style="min-height:70px">{{ old('excerpt', $post->excerpt) }}</textarea>
        </div>

        <div class="card">
            <label>Текст статьи</label>
            <input id="body" type="hidden" name="body" value="{{ old('body', $post->body) }}">
            <trix-editor input="body"></trix-editor>
            <p class="hint">Жирный, курсив, заголовки, списки, цитаты, ссылки. Форматирование сохраняется как есть.</p>
        </div>

        <div class="card">
            <label>Статус</label>
            <div class="row">
                <div>
                    <select name="status">
                        <option value="draft" @selected(old('status', $post->status ?: 'draft') === 'draft')>Черновик (не виден на сайте)</option>
                        <option value="published" @selected(old('status', $post->status) === 'published')>Опубликована</option>
                    </select>
                </div>
                <div style="display:flex;align-items:flex-end">
                    <button class="btn primary">{{ $post->exists ? 'Сохранить' : 'Создать' }}</button>
                </div>
            </div>
        </div>
    </form>
@endsection
