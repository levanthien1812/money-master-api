<?php

namespace Tests\Unit\Services;

use App\Http\Services\AuthService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    use RefreshDatabase;

    private $authService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authService = app(AuthService::class);
    }

    public function test_register_user_fail_email_exists()
    {
        $registerData = [
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        Role::create(['name' => 'user', 'guard_name' => 'api']);

        $resultData = $this->authService->register($registerData);

        $this->assertTrue($resultData->status === 'success');
    }

    public function test_register_user()
    {
        $registerData = [
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        Role::create(['name' => 'user', 'guard_name' => 'api']);

        $resultData = $this->authService->register($registerData);

        $this->assertTrue($resultData->status === 'success');
    }

    public function test_generate_verification_code()
    {
        $codeLength = 6;

        $code = $this->authService->_generateVerificationCode($codeLength);

        $this->assertTrue(strlen($code) === $codeLength);
    }

    public function test_send_verification_code()
    {
        $email = fake()->safeEmail();
        $resultData = $this->authService->sendVerificationCode($email);

        $this->assertTrue($resultData->status === 'success');
    }

    public function test_login_fail_user_not_found()
    {
        $loginData = [
            'email' => fake()->safeEmail(),
            'password' => Str::random(8),
        ];

        $resultData = $this->authService->login($loginData);
        $this->assertEquals($resultData->message, 'User not found!');
    }

    public function test_login_fail_wrong_password()
    {
        $existingUser = User::factory()->create();

        $loginData = [
            'email' => $existingUser->email,
            'password' => 'afsnkfdlslfks', //random password that not equals to user password
        ];

        $resultData = $this->authService->login($loginData);
        $this->assertEquals($resultData->message, 'Password is not correct!');
    }

    public function test_login()
    {
        $existingUser = User::factory()->create();

        $loginData = [
            'email' => $existingUser->email,
            'password' => 'password',
        ];

        $resultData = $this->authService->login($loginData);
        $this->assertTrue($resultData->status === 'success');
    }

    public function test_forget_password_fail_email_not_exists()
    {
        $email = fake()->email();

        $resultData = $this->authService->forgetPassword($email);
        $this->assertEquals($resultData->message, 'User with this email does not exist!');
    }

    public function test_logout()
    {
        $existingUser = User::factory()->create();
        $loggedInUser = $this->authService->login(['email' => $existingUser->email, 'password' => 'password'])->data['user'];

        $resultData = $this->authService->logout($loggedInUser);
        $this->assertTrue($resultData->status === 'success');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
