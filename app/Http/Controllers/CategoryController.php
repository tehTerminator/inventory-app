<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;

class CategoryController extends Controller
{
    public function __construct()
    {
    }

    public function fetch()
    {
        return response()->json(Category::all());
    }

    public function create(Request $request)
    {
        $this->validate($request, [
            'title' => ['required', 'unique:categories,title']
        ]);

        $category = Category::create(['title' => $request->title]);

        return response()->json($category);
    }

    public function update(Request $request)
    {
        $this->validate($request, [
            'id' => ['required', 'numeric', 'min:1', 'exists:categories,id'],
            'title' => ['required', 'unique:categories,title']
        ]);

        $category = Category::findOrFail($request->id);
        $category->title = $request->title;
        $category->save();

        return response()->json($category);
    }
}
