<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Laravel\Sanctum\Sanctum;

class UserEmailTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_update_email()
    {
        $user = User::factory()->create(['email' => 'old@example.com']);
        Sanctum::actingAs($user);
        $response = $this->postJson('/api/update-user-email', [
            'email' => 'new@example.com',
        ]);
        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);
        $user->refresh();
        $this->assertEquals('new@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_cannot_update_to_existing_email()
    {
        User::factory()->create(['email' => 'existing@example.com']);
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $response = $this->postJson('/api/update-user-email', [
            'email' => 'existing@example.com',
        ]);
        $response->assertStatus(422);
    }

    public function test_user_can_verify_email()
    {
        $user = User::factory()->unverified()->create();
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->getEmailForVerification())]
        );
        // Extract path from full URL
        $path = parse_url($verificationUrl, PHP_URL_PATH);
        $response = $this->get($path);
        $response->assertStatus(302); // redirect
        $user->refresh();
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_send_verification_email()
    {
        $user = User::factory()->unverified()->create();
        Sanctum::actingAs($user);
        $response = $this->postJson('/api/send-verification-email');
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }
}