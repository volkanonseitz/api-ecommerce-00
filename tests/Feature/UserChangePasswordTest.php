<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;

class UserChangePasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_change_password()
    {
        $user = User::factory()->create(['password' => Hash::make('oldpass')]);
        Sanctum::actingAs($user);
        $response = $this->postJson('/api/change-password', [
            'oldPassword' => 'oldpass',
            'newPassword' => 'newpass123',
        ]);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $user->refresh();
        $this->assertTrue(Hash::check('newpass123', $user->password));
    }

    public function test_change_password_fails_with_wrong_old_password()
    {
        $user = User::factory()->create(['password' => Hash::make('oldpass')]);
        Sanctum::actingAs($user);
        $response = $this->postJson('/api/change-password', [
            'oldPassword' => 'wrong',
            'newPassword' => 'newpass123',
        ]);
        $response->assertStatus(200);
        $response->assertJson(['success' => false]);
    }
}