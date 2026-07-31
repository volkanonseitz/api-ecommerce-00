<?php

namespace Database\Factories;

use App\Enums\Permission as PermissionEnum;
use App\Models\Permission;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Permission>
 */
class PermissionFactory extends Factory
{
    protected $model = Permission::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word(),
            'guard_name' => 'api',
        ];
    }

    public function superAdmin(): self
    {
        return $this->state(fn (array $attributes) => [
            'name' => PermissionEnum::SUPER_ADMIN->value,
        ]);
    }

    public function storeOwner(): self
    {
        return $this->state(fn (array $attributes) => [
            'name' => PermissionEnum::STORE_OWNER->value,
        ]);
    }

    public function staff(): self
    {
        return $this->state(fn (array $attributes) => [
            'name' => PermissionEnum::STAFF->value,
        ]);
    }

    public function customer(): self
    {
        return $this->state(fn (array $attributes) => [
            'name' => PermissionEnum::CUSTOMER->value,
        ]);
    }
}
