<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Shop;
use App\Enums\Permission;
use App\Enums\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $vendor;
    private User $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create();
        $this->admin->givePermissionTo(Permission::SUPER_ADMIN->value);
        $this->admin->assignRole(Role::SUPER_ADMIN->value);

        $this->vendor = User::factory()->create();
        $this->vendor->givePermissionTo(Permission::STORE_OWNER->value);
        $this->vendor->assignRole(Role::STORE_OWNER->value);

        $this->customer = User::factory()->create();
        $this->customer->givePermissionTo(Permission::CUSTOMER->value);
        $this->customer->assignRole(Role::CUSTOMER->value);
    }

    public function test_admin_can_list_all_users()
    {
        Sanctum::actingAs($this->admin);
        $response = $this->getJson('/api/users');
        $response->assertStatus(200);
        $response->assertJsonCount(3, 'data'); // admin, vendor, customer
    }

    public function test_admin_can_view_single_user()
    {
        Sanctum::actingAs($this->admin);
        $response = $this->getJson('/api/users/' . $this->customer->id);
        $response->assertStatus(200);
        $response->assertJson(['id' => $this->customer->id]);
    }

    public function test_admin_can_update_user()
    {
        Sanctum::actingAs($this->admin);
        $response = $this->putJson('/api/users/' . $this->customer->id, [
            'name' => 'Updated Name',
        ]);
        $response->assertStatus(200);
        $this->assertDatabaseHas('users', ['id' => $this->customer->id, 'name' => 'Updated Name']);
    }

    public function test_user_can_update_own_profile()
    {
        Sanctum::actingAs($this->customer);
        $response = $this->putJson('/api/users/' . $this->customer->id, [
            'name' => 'My New Name',
        ]);
        $response->assertStatus(200);
        $this->assertDatabaseHas('users', ['id' => $this->customer->id, 'name' => 'My New Name']);
    }

    public function test_user_cannot_update_other_user()
    {
        Sanctum::actingAs($this->customer);
        $other = User::factory()->create();
        $response = $this->putJson('/api/users/' . $other->id, [
            'name' => 'Hacked',
        ]);
        $response->assertStatus(403);
    }

    public function test_admin_can_ban_user()
    {
        Sanctum::actingAs($this->admin);
        $response = $this->postJson('/api/ban-user', ['id' => $this->customer->id]);
        $response->assertStatus(200);
        $this->assertDatabaseHas('users', ['id' => $this->customer->id, 'is_active' => false]);
    }

    public function test_admin_can_activate_user()
    {
        $this->customer->update(['is_active' => false]);
        Sanctum::actingAs($this->admin);
        $response = $this->postJson('/api/active-user', ['id' => $this->customer->id]);
        $response->assertStatus(200);
        $this->assertDatabaseHas('users', ['id' => $this->customer->id, 'is_active' => true]);
    }

    public function test_non_admin_cannot_ban_user()
    {
        Sanctum::actingAs($this->vendor);
        $response = $this->postJson('/api/ban-user', ['id' => $this->customer->id]);
        $response->assertStatus(403);
    }

    public function test_admins_endpoint_returns_only_super_admins()
    {
        Sanctum::actingAs($this->admin);
        $response = $this->getJson('/api/admins');
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.id', $this->admin->id);
    }

    public function test_vendors_endpoint_returns_store_owners()
    {
        Sanctum::actingAs($this->admin);
        $response = $this->getJson('/api/vendors');
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.id', $this->vendor->id);
    }

    public function test_customers_endpoint_returns_only_customers()
    {
        Sanctum::actingAs($this->admin);
        $response = $this->getJson('/api/customers');
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.id', $this->customer->id);
    }

    public function test_me_endpoint_returns_logged_in_user_with_relations()
    {
        Sanctum::actingAs($this->customer);
        $response = $this->getJson('/api/me');
        $response->assertStatus(200);
        $response->assertJson(['id' => $this->customer->id]);
        $response->assertJsonStructure(['profile', 'wallet', 'address']);
    }
}