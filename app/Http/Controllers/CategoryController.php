<?php

namespace App\Http\Controllers;

use App\Services\CategoryService;
use App\Http\Requests\CategoryCreateRequest;
use App\Http\Requests\CategoryUpdateRequest;
use App\Http\Resources\CategoryResource;
use App\DTO\CategoryData;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct(private CategoryService $categoryService) {}

    public function index(Request $request)
    {
        $language = $request->language ?? config('constants.DEFAULT_LANGUAGE', 'en');
        $parent = $request->parent ?? null;
        $selfId = $request->self ?? null;
        $limit = $request->limit ?? 15;
        $categories = $this->categoryService->getCategories($language, $parent, $selfId, $limit);
        return CategoryResource::collection($categories);
    }

    public function store(CategoryCreateRequest $request)
    {
        $data = CategoryData::fromRequest($request->validated());
        $category = $this->categoryService->createCategory($data);
        return new CategoryResource($category);
    }

    public function show(Request $request, $params)
    {
        $language = $request->language ?? config('constants.DEFAULT_LANGUAGE', 'en');
        $category = $this->categoryService->getCategoryByIdOrSlug($params, $language);
        return new CategoryResource($category);
    }

    public function update(CategoryUpdateRequest $request, $id)
    {
        $category = Category::findOrFail($id);
        $data = CategoryData::fromRequest($request->validated());
        $updated = $this->categoryService->updateCategory($category, $data);
        return new CategoryResource($updated);
    }

    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $this->categoryService->deleteCategory($category);
        return response()->json(['message' => 'Category deleted']);
    }
}