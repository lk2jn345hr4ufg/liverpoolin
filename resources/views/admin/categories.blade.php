@extends('layouts.admin')
@section('title', 'Categories')

@section('content')
    <h1>Категории</h1>

    <div class="card">
        <h3>Добавить категорию</h3>
        <p class="muted">Описание передаётся в Gemini — по нему ИИ решает, к какой категории отнести статью. Чем точнее описание, тем точнее классификация.</p>
        <form method="post" action="{{ route('admin.categories.store') }}">
            @csrf
            <div class="row">
                <div><label>Название</label><input name="name" required placeholder="Например: Трансферы"></div>
                <div><label>Цвет</label><input type="color" name="color" value="#c8102e" style="height:38px;padding:3px"></div>
                <div><label>Позиция</label><input type="number" name="position" value="0" min="0" max="999"></div>
            </div>
            <div style="margin-top:12px">
                <label>Описание для ИИ</label>
                <input name="description" placeholder="Какие новости сюда относятся">
            </div>
            <div style="margin-top:12px"><button class="btn primary">Добавить</button></div>
        </form>
    </div>

    <div class="card">
        <h3>Существующие категории</h3>
        <table>
            <thead><tr><th>Название</th><th>Slug</th><th>Статей</th><th>Описание для ИИ</th><th></th></tr></thead>
            <tbody>
            @forelse($categories as $c)
                <tr>
                    <td>
                        <form class="inline" method="post" action="{{ route('admin.categories.update', $c) }}">
                            @csrf @method('PUT')
                            <span style="display:inline-block;width:12px;height:12px;border-radius:3px;background:{{ $c->color }};vertical-align:middle;margin-right:6px"></span>
                            <input name="name" value="{{ $c->name }}" style="width:150px;display:inline-block">
                            <input type="hidden" name="color" value="{{ $c->color }}">
                            <input type="hidden" name="position" value="{{ $c->position }}">
                    </td>
                    <td class="muted"><code>{{ $c->slug }}</code></td>
                    <td>{{ $c->articles_count }}</td>
                    <td><input name="description" value="{{ $c->description }}" style="width:100%"></td>
                    <td>
                            <button class="btn ghost">Сохранить</button>
                        </form>
                        <form class="inline" method="post" action="{{ route('admin.categories.destroy', $c) }}"
                              onsubmit="return confirm('Удалить категорию? Статьи останутся, но без категории.')">
                            @csrf @method('DELETE')<button class="btn danger">Удалить</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="muted">Категорий пока нет.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
