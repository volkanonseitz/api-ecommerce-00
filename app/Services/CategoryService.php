<?php

namespace App\Services;

use App\Models\Category;
use App\DTO\CategoryData;
use App\Actions\CreateCategoryAction;
use App\Actions\UpdateCategoryAction;

class CategoryService
{
    public function __construct(
        private CreateCategoryAction $createCategory,
        private UpdateCategoryAction $updateCategory,
    ) {}

    public function getCategories(string $language, $parent = null, $selfId = null, int $perPage = 15)
    {
        $query = Category::with(['type', 'parentCategory', 'children'])
            ->where('language', $language)
            ->withCount('products');

        if ($parent === 'null') {
            $query->whereNull('parent');
        }
        if ($selfId) {
            $query->where('id', '!=', $selfId);
        }

        return $query->paginate($perPage);
    }

    public function getCategoryByIdOrSlug($param, string $language)
    {
        if (is_numeric($param)) {
            return Category::with(['type', 'parentCategory', 'children'])->where('id', $param)->firstOrFail();
        }
        return Category::with(['type', 'parentCategory', 'children'])->where('slug', $param)->firstOrFail();
    }

    public function createCategory(CategoryData $data): Category
    {
        return $this->createCategory->execute($data);
    }

    public function updateCategory(Category $category, CategoryData $data): Category
    {
        return $this->updateCategory->execute($category, $data);
    }

    public function deleteCategory(Category $category): void
    {
        $category->delete();
    }
}