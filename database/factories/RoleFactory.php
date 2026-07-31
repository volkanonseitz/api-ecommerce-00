<?php

namespace Database\Factories;

use App\Enums\Role as RoleEnum;
use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Role>
 */
class RoleFactory extends Factory
{
    protected $model = Role::class;

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
            'name' => RoleEnum::SUPER_ADMIN->value,
        ]);
    }

    public function storeOwner(): self
    {
        return $this->state(fn (array $attributes) => [
            'name' => RoleEnum::STORE_OWNER->value,
        ]);
    }

    public function staff(): self
    {
        return $this->state(fn (array $attributes) => [
            'name' => RoleEnum::STAFF->value,
        ]);
    }

    public function customer(): self
    {
        return $this->state(fn (array $attributes) => [
            'name' => RoleEnum::CUSTOMER->value,
        ]);
    }
}
