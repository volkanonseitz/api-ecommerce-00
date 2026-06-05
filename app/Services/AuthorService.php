<?php

namespace App\Services;

use App\Actions\CreateAuthorAction;
use App\Actions\UpdateAuthorAction;
use App\DTO\AuthorData;
use App\Enums\Permission;
use App\Models\Author;
use App\Models\Shop;
use Illuminate\Contracts\Auth\Authenticatable;

class AuthorService
{
    public function __construct(
        private CreateAuthorAction $createAuthor,
        private UpdateAuthorAction $updateAuthor,
    ) {}

    public function hasPermission(?Authenticatable $user, ?int $shopId): bool
    {
        if (! $user) {
            return false;
        }
        if ($user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            return true;
        }

        if (! $shopId) {
            return false;
        }

        $shop = Shop::find($shopId);
        if (! $shop || ! $shop->is_active) {
            throw new \Exception(config('notice.SHOP_NOT_APPROVED'));
        }

        if ($user->hasPermissionTo(Permission::STORE_OWNER->value)) {
            return $shop->owner_id === $user->id;
        }

        // Staff tidak diizinkan mengelola author (kecuali jika diinginkan, bisa ditambahkan)
        return false;
    }

    public function getAuthorsByLanguage(
        string $language,
        int $perPage = 15
    ) {
        return Author::query()
            ->where('language', $language)
            ->withCount('products')
            ->paginate($perPage);
    }

    public function getAuthorBySlug(string $slug, string $language): ?Author
    {
        return Author::where('slug', $slug)->where('language', $language)->firstOrFail();
    }

    public function createAuthor(AuthorData $data): Author
    {
        return $this->createAuthor->execute($data);
    }

    public function updateAuthor(Author $author, AuthorData $data): Author
    {
        return $this->updateAuthor->execute($author, $data);
    }

    public function deleteAuthor(Author $author): void
    {
        $author->delete();
    }

    public function getTopAuthors(
        string $language,
        int $limit = 10
    ) {
        return Author::query()
            ->where('language', $language)
            ->withCount('products')
            ->orderByDesc('products_count')
            ->limit($limit)
            ->get();
    }
}
