<?php

namespace App\Http\Controllers\Back;

use App\Models\Category;
use App\Http\Controllers\Controller;
use Cviebrock\EloquentSluggable\Services\SlugService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    public $ordering = 1;

    public function store(Request $request)
    {
        $this->validate($request, [
            'title' => 'required|string',
            'type'  => 'required|string',
            'slug'  => 'nullable|unique:categories,slug',
        ]);

        $this->authorizeCategory($request->type);

        $category = Category::create([
            'title' => $request->title,
            'lang'  => app()->getLocale(),
            'type'  => $request->type,
            'slug'  => $request->slug ?: $request->title,
        ]);

        return $category;
    }

    public function edit(Category $category)
    {
        $this->authorizeCategory($category->type);

        if ($category->type == 'productcat') {
            return view('back.products.categories.edit', compact('category'));
        }

        return view('back.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $this->authorizeCategory($category->type);

        $this->validate($request, [
            'title' => 'required|string',
            'image' => 'image',
            'slug'  => "nullable|unique:categories,slug,$category->id",
            'commission'  => "nullable|numeric|max:100",
        ]);

        $category->update([
            'title'            => $request->title,
            'slug'             => $request->slug ?: $request->title,
            'meta_title'       => $request->meta_title,
            'meta_description' => $request->meta_description,
            'description'      => $request->description,
            'filter_type'      => $request->filter_type ?: 'inherit',
            'filter_id'        => $request->filter_id,
            'commission'        => $request->commission,
            'published'        => $request->has('published'),
        ]);

        if ($request->hasFile('image')) {

            $category->image=uploadOptimizedImage($request->image, 'categories',$category->id);

            $category->save();
        }

        if ($request->hasFile('background_image')) {

            $category->background_image = uploadOptimizedImage($request->image, 'categories',$category->id);
            $category->save();
        }

        return $category;
    }

    public function destroy(Category $category)
    {
        $this->authorizeCategory($category->type);

        foreach (Category::whereIn('id', $category->allChildCategories())->get() as $child_category) {
            if ($child_category->image){
                Storage::disk('public')->delete($child_category->image);
            }
            if ($child_category->background_image){
                Storage::disk('public')->delete($child_category->background_image);
            }

            $child_category->menus()->detach();
            $child_category->delete();
        }

        return $category;
    }

    public function sort(Request $request)
    {
        $this->validate($request, [
            'categories' => 'required|array',
            'type'       => 'required'
        ]);

        $this->authorizeCategory($request->type);

        $categories = $request->categories;

        $this->sort_category($categories);

        return 'success';
    }

    private function sort_category($categories, $category_id = null)
    {
        foreach ($categories as $category) {
            Category::find($category['id'])->update(['category_id' => $category_id, 'ordering' => $this->ordering++]);
            if (array_key_exists('children', $category)) {
                $this->sort_category($category['children'], $category['id']);
            }
        }
    }

    private function authorizeCategory($type)
    {
        switch ($type) {
            case "postcat": {
                    $this->authorize('posts.category');
                    break;
                }
            case "productcat": {
                    $this->authorize('products.category');
                    break;
                }
        }
    }

    public function generate_slug(Request $request)
    {
        $request->validate([
            'title' => 'required',
        ]);

        $slug = SlugService::createSlug(Category::class, 'slug', $request->title);

        return response()->json(['slug' => $slug]);
    }
}
