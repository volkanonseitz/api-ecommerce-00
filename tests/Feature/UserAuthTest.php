<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class UserAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_as_customer()
    {
        // Create settings dummy
        Settings::create(['language' => 'en', 'options' => ['useMustVerifyEmail' => false]]);

        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['token', 'permissions', 'role']);

        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
        $user = User::where('email', 'test@example.com')->first();
        $this->assertTrue($user->hasPermissionTo(\App\Enums\Permission::CUSTOMER->value));
    }

    public function test_user_cannot_register_with_existing_email()
    {
        User::factory()->create(['email' => 'existing@example.com']);
        $response = $this->postJson('/api/register', [
            'name' => 'Another',
            'email' => 'existing@example.com',
            'password' => 'password',
        ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('email');
    }

    public function test_user_can_login_with_valid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'login@example.com',
            'password' => Hash::make('secret'),
            'is_active' => true,
        ]);
        $user->givePermissionTo(\App\Enums\Permission::CUSTOMER->value);

        $response = $this->postJson('/api/token', [
            'email' => 'login@example.com',
            'password' => 'secret',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['token', 'permissions', 'email_verified', 'role']);
        $response->assertJson(['email_verified' => true]);
    }

    public function test_login_fails_with_invalid_password()
    {
        $user = User::factory()->create(['email' => 'login@example.com', 'password' => Hash::make('secret')]);
        $response = $this->postJson('/api/token', [
            'email' => 'login@example.com',
            'password' => 'wrong',
        ]);
        $response->assertStatus(200); // tetap 200 tapi token null
        $response->assertJson(['token' => null]);
    }

    public function test_login_fails_if_inactive()
    {
        $user = User::factory()->inactive()->create(['email' => 'inactive@example.com', 'password' => Hash::make('secret')]);
        $response = $this->postJson('/api/token', [
            'email' => 'inactive@example.com',
            'password' => 'secret',
        ]);
        $response->assertJson(['token' => null]);
    }

    public function test_user_can_logout()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/logout');
        $response->assertStatus(200);
        $this->assertCount(0, $user->tokens);
    }
}