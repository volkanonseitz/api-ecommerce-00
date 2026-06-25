<?php

namespace App\Http\Controllers;

use App\DTO\CategoryData;
use App\Enums\Permission;
use App\Http\Requests\CategoryCreateRequest;
use App\Http\Requests\CategoryUpdateRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;

class CategoryController extends BaseController
{
    public function __construct(private CategoryService $categoryService) {}

    public function index(Request $request)
    {
        $language = $request->language ?? config('shop.default_language', 'id');
        $parent = $request->parent ?? null;
        $selfId = $request->self ?? null;
        $limit = $request->limit ?? 15;
        $categories = $this->categoryService->getCategories($language, $parent, $selfId, $limit);

        return $this->sendPaginated(
            $categories,
            CategoryResource::collection($categories->getCollection()),
            'Daftar category berhasil diambil.'
        );
    }

    public function store(CategoryCreateRequest $request)
    {
        $user = $request->user();
        if (! $user || ! $user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        $data = CategoryData::fromRequest($request->validated());
        $category = $this->categoryService->createCategory($data);

        return $this->sendSuccess(new CategoryResource($category), 'Category created', 201);
    }

    public function show(Request $request, string $params)
    {
        $language = $request->language ?? config('shop.default_language', 'id');
        $category = $this->categoryService->getCategoryByIdOrSlug($params, $language);

        return $this->sendSuccess(new CategoryResource($category), 'Category detail');
    }

    public function update(CategoryUpdateRequest $request, $id)
    {
        $user = $request->user();
        if (! $user || ! $user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        $category = Category::findOrFail($id);
        $data = CategoryData::fromRequest($request->validated());
        $updated = $this->categoryService->updateCategory($category, $data);

        return $this->sendSuccess(new CategoryResource($updated), 'Category updated');
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        if (! $user || ! $user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        $category = Category::findOrFail($id);
        $this->categoryService->deleteCategory($category);

        return $this->sendSuccess(null, 'Category deleted');
    }

    public function fetchFeaturedCategories(Request $request)
    {
        $categories = $this->categoryService->fetchFeaturedCategories();

        return $this->sendSuccess($categories, 'Featured categories');
    }
}
