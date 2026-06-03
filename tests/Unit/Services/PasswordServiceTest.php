<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\PasswordService;
use App\Services\UserService;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PasswordServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PasswordService $passwordService;

    protected function setUp(): void
    {
        parent::setUp();
        $userService = $this->createMock(UserService::class);
        $userService->method('sendResetEmail')->willReturn(true);
        $this->passwordService = new PasswordService($userService);
    }

    public function test_forget_password_creates_token_and_returns_success()
    {
        $user = User::factory()->create();
        $result = $this->passwordService->forgetPassword($user->email);
        $this->assertTrue($result['success']);
        $this->assertDatabaseHas('password_resets', ['email' => $user->email]);
    }

    public function test_forget_password_user_not_found()
    {
        $result = $this->passwordService->forgetPassword('nonexistent@example.com');
        $this->assertFalse($result['success']);
        $this->assertEquals(config('notice.NOT_FOUND'), $result['message']);
    }

    public function test_verify_token_valid()
    {
        $user = User::factory()->create();
        $token = 'valid_token';
        DB::table('password_resets')->insert([
            'email' => $user->email,
            'token' => $token,
            'created_at' => now(),
        ]);
        $result = $this->passwordService->verifyToken($user->email, $token);
        $this->assertTrue($result['success']);
    }

    public function test_verify_token_invalid()
    {
        $result = $this->passwordService->verifyToken('email@example.com', 'wrong');
        $this->assertFalse($result['success']);
    }

    public function test_reset_password_updates_password()
    {
        $user = User::factory()->create(['password' => Hash::make('old')]);
        $token = 'reset_token';
        DB::table('password_resets')->insert([
            'email' => $user->email,
            'token' => $token,
            'created_at' => now(),
        ]);
        $result = $this->passwordService->resetPassword($user->email, $token, 'newpassword');
        $this->assertTrue($result['success']);
        $user->refresh();
        $this->assertTrue(Hash::check('newpassword', $user->password));
        $this->assertDatabaseMissing('password_resets', ['email' => $user->email]);
    }

    public function test_reset_password_invalid_token()
    {
        $user = User::factory()->create();
        $result = $this->passwordService->resetPassword($user->email, 'invalid', 'newpass');
        $this->assertFalse($result['success']);
        $this->assertEquals(config('notice.INVALID_TOKEN'), $result['message']);
    }

    public function test_change_password_success()
    {
        $user = User::factory()->create(['password' => Hash::make('oldpass')]);
        $result = $this->passwordService->changePassword($user, 'oldpass', 'newpass');
        $this->assertTrue($result['success']);
        $user->refresh();
        $this->assertTrue(Hash::check('newpass', $user->password));
    }

    public function test_change_password_incorrect_old()
    {
        $user = User::factory()->create(['password' => Hash::make('oldpass')]);
        $result = $this->passwordService->changePassword($user, 'wrong', 'newpass');
        $this->assertFalse($result['success']);
        $this->assertEquals(config('notice.OLD_PASSWORD_INCORRECT'), $result['message']);
    }
}