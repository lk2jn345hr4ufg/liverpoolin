<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::with('category')->latest()->paginate(20);

        return view('admin.posts', compact('posts'));
    }

    public function create()
    {
        return view('admin.post_form', [
            'post'       => new Post(['author' => 'Редакция LiverpoolIn']),
            'categories' => Category::ordered()->get(),
        ]);
    }

    public function edit(Post $post)
    {
        return view('admin.post_form', [
            'post'       => $post,
            'categories' => Category::ordered()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $post = Post::create($this->validated($request));

        return redirect()
            ->route('admin.posts.edit', $post)
            ->with('ok', 'Статья создана.');
    }

    public function update(Request $request, Post $post)
    {
        $post->update($this->validated($request));

        return back()->with('ok', 'Сохранено.');
    }

    public function publish(Post $post)
    {
        $post->publish();

        return back()->with('ok', 'Статья опубликована.');
    }

    public function unpublish(Post $post)
    {
        $post->unpublish();

        return back()->with('ok', 'Статья снята с публикации.');
    }

    public function destroy(Post $post)
    {
        $post->delete();

        return redirect()->route('admin.posts')->with('ok', 'Статья удалена.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title'       => 'required|string|max:300',
            'excerpt'     => 'nullable|string|max:500',
            'body'        => 'nullable|string',
            'image_url'   => 'nullable|url|max:1000',
            'category_id' => 'nullable|exists:categories,id',
            'author'      => 'nullable|string|max:120',
            'status'      => 'required|in:draft,published',
        ]);
    }
}
