<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\AuthService;
use App\Services\UserService;
use App\Services\WalletService;
use App\Models\User;
use App\Models\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class AuthServiceTest extends TestCase
{
    use RefreshDatabase;

    protected AuthService $authService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authService = new AuthService(
            new UserService(new \App\Actions\CreateUserAction(), new \App\Actions\UpdateUserAction(), new WalletService()),
            new WalletService()
        );
    }

    public function test_attempt_login_success()
    {
        $user = User::factory()->create(['password' => Hash::make('secret')]);
        $result = $this->authService->attemptLogin($user->email, 'secret', true);
        $this->assertNotNull($result);
        $this->assertArrayHasKey('token', $result);
        $this->assertEquals($user->getPermissionNames(), $result['permissions']);
    }

    public function test_attempt_login_invalid_password()
    {
        $user = User::factory()->create(['password' => Hash::make('secret')]);
        $result = $this->authService->attemptLogin($user->email, 'wrong', true);
        $this->assertNull($result);
    }

    public function test_attempt_login_app_invalid()
    {
        $user = User::factory()->create(['password' => Hash::make('secret')]);
        $result = $this->authService->attemptLogin($user->email, 'secret', false);
        $this->assertNull($result);
    }

    public function test_register_creates_user_with_customer_permission()
    {
        $data = \App\DTO\UserData::fromRequest([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password',
        ]);
        $result = $this->authService->register($data, false);
        $this->assertArrayHasKey('token', $result);
        $user = User::where('email', 'john@example.com')->first();
        $this->assertTrue($user->hasPermissionTo(\App\Enums\Permission::CUSTOMER->value));
    }
}