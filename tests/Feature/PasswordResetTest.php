<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_request_password_reset()
    {
        $user = User::factory()->create();
        $response = $this->postJson('/api/forget-password', ['email' => $user->email]);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('password_resets', ['email' => $user->email]);
    }

    public function test_user_can_verify_reset_token()
    {
        $user = User::factory()->create();
        $token = 'testtoken123';
        DB::table('password_resets')->insert([
            'email' => $user->email,
            'token' => $token,
            'created_at' => now(),
        ]);
        $response = $this->postJson('/api/verify-forget-password-token', [
            'email' => $user->email,
            'token' => $token,
        ]);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_user_can_reset_password()
    {
        $user = User::factory()->create(['password' => Hash::make('oldpass')]);
        $token = 'resettoken';
        DB::table('password_resets')->insert([
            'email' => $user->email,
            'token' => $token,
            'created_at' => now(),
        ]);
        $response = $this->postJson('/api/reset-password', [
            'email' => $user->email,
            'token' => $token,
            'password' => 'newpassword123',
        ]);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $user->refresh();
        $this->assertTrue(Hash::check('newpassword123', $user->password));
    }

    public function test_reset_password_fails_with_invalid_token()
    {
        $user = User::factory()->create();
        $response = $this->postJson('/api/reset-password', [
            'email' => $user->email,
            'token' => 'invalid',
            'password' => 'newpass',
        ]);
        $response->assertStatus(200);
        $response->assertJson(['success' => false, 'message' => config('notice.INVALID_TOKEN')]);
    }
}