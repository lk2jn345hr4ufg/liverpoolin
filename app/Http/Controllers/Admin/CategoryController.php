<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::ordered()->withCount('articles')->get();

        return view('admin.categories', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug'] = Category::makeSlug($data['name']);

        // Guarantee uniqueness if two names transliterate the same.
        $base = $data['slug'];
        $i = 2;
        while (Category::where('slug', $data['slug'])->exists()) {
            $data['slug'] = $base . '-' . $i++;
        }

        Category::create($data);

        return back()->with('ok', 'Категория добавлена.');
    }

    public function update(Request $request, Category $category)
    {
        $category->update($this->validated($request, $category));

        return back()->with('ok', 'Категория обновлена.');
    }

    public function destroy(Category $category)
    {
        // Articles keep existing; their category_id is set to null.
        $category->delete();

        return back()->with('ok', 'Категория удалена. Статьи остались без категории.');
    }

    private function validated(Request $request, ?Category $category = null): array
    {
        return $request->validate([
            'name'        => ['required', 'string', 'max:80'],
            'color'       => ['nullable', 'string', 'max:9'],
            'description' => ['nullable', 'string', 'max:500'],
            'position'    => ['nullable', 'integer', 'min:0', 'max:999'],
        ]);
    }
}
