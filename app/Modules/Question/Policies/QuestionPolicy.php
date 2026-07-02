<?php

declare(strict_types=1);

namespace App\Modules\Question\Policies;

use App\Enums\Permission;
use App\Models\Question;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class QuestionPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // All authenticated users can view questions
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Question $question): bool
    {
        return $user->hasPermissionTo(Permission::SUPER_ADMIN->value)
            || $user->id === $question->user_id;
        // Optionally add check for shop owner/staff if they can view all questions for their shop
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true; // All authenticated users can create questions
    }

    /**
     * Determine whether the user can update the model (answer the question).
     */
    public function update(User $user, Question $question): bool
    {
        if ($user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            return true;
        }

        if ($user->hasPermissionTo(Permission::STORE_OWNER->value) && $question->shop_id) {
            $shop = Shop::find($question->shop_id);

            return $shop && $shop->owner_id === $user->id;
        }

        if ($user->hasPermissionTo(Permission::STAFF->value) && $question->shop_id) {
            $shop = Shop::find($question->shop_id);

            return $shop && $shop->staffs->contains($user->id);
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Question $question): bool
    {
        return $user->hasPermissionTo(Permission::SUPER_ADMIN->value)
            || $user->id === $question->user_id;
    }
}
