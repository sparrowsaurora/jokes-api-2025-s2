<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        // validation
        $validated = $request->validate([
            'perPage' => ['integer', 'nullable'],
            'page' => ['integer', 'nullable'],
            'search' => ['nullable', 'string', 'max:32'],
        ]);

        $perPage = $validated['perPage'] ?? 12;
        $page = $validated['page'] ?? 1;
        $search = $validated['search'] ?? '';

        // get categories
        $categories = Category::where('title', 'like', '%'.$search.'%')
            ->orderBy('title')
            ->withCount('jokes')
            ->paginate($perPage, ['*'], 'page', $page);

        // TODO: get trash category count
        $trashCount = 0;

        //return view
        return view('admin.categories.index')
            ->with('categories', $categories)
            ->with('trashCount', $trashCount)
            ->with('search', $search);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => [
                'required',
                'string',
                'min:4',
                'max:32',
                'unique:categories,title',
            ],
            'description' => [
                'nullable',
                'string',
                'min:16',
                'max:128',
            ],
        ]);

        $category = Category::create($validated);
        return redirect()->route('admin.categories.index')
            ->with('success', "Category {$category->title} added successfully");
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category): View
    {
        $categoryId = $category->id;

        $category = Category::where('id', $categoryId)
            ->withCount('jokes')
            ->first();

        $jokes = $category->jokesByDateAddedDesc()->limit(5)->get();
        return view('admin.categories.show')
            ->with('first', $jokes)
            ->with('category', $category);

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category): View
    {
        return view('admin.categories.edit')
            ->with('category', $category);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'title' => [
                'required',
                'string',
                'min:4',
                'max:32',
                'unique:categories,title',
            ],
            'description' => [
                'nullable',
                'string',
                'min:16',
                'max:128',
            ],
        ]);

        $category->update($validated);

        $category = Category::create($validated);
        return redirect()->route('admin.categories.index')
            ->with('success', "Category {$category->title} added successfully");
    }

    public function delete(Category $category): View
    {
        return view('admin.categories.delete')
            ->with('category', $category);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        $categoryTitle = $category->title;

        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', "Category {$categoryTitle} added successfully");
    }
}
