<?php

namespace Tests\Unit\Controllers;

use App\Http\Controllers\Api\v1\AuthController;
use App\Http\Requests\Users\LoginRequest;
use App\Http\Requests\Users\RegisterUserRequest;
use App\Http\Requests\Users\SendVerficationCodeRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    use RefreshDatabase;

    private $authController;

    private $user;

    protected function setUp(): void
    {
        parent::setUp();
        Role::create(['name' => 'admin', 'guard_name' => 'api']);
        Role::create(['name' => 'user', 'guard_name' => 'api']);

        $this->authController = app(AuthController::class);
    }

    public function test_register_fail_email_exists()
    {
        $user = User::factory()->create();

        $data = [
            'name' => fake()->name(),
            'email' => $user->email,
            'password' => '12345678',
            'password_confirmation' => '12345678',
        ];

        $request = RegisterUserRequest::create('/api/v1/register', 'POST');
        $request->merge($data);

        $response = $this->authController->register($request);
        $this->assertEquals($response->getData()->status, 'failed');
    }

    public function test_send_verification_code()
    {
        $user = User::factory()->create();

        $data = [
            'email' => $user->email,
        ];

        $request = SendVerficationCodeRequest::create('/api/v1/send-verification-code', 'POST');
        $request->merge($data);

        $response = $this->authController->sendVerificationCode($request);
        $this->assertEquals($response->getData()->status, 'success');
    }

    public function test_login_fail_email_not_found()
    {
        $data = [
            'email' => fake()->email(),
            'password' => 'password',
        ];

        $request = LoginRequest::create('/api/v1/login', 'POST');
        $request->merge($data);

        $response = $this->authController->login($request);
        $this->assertEquals($response->getData()->status, 'failed');
    }

    public function test_login_fail_password_incorrect()
    {
        $user = User::factory()->create();

        $data = [
            'email' => $user->email,
            'password' => 'klkfsjas',
        ];

        $request = LoginRequest::create('/api/v1/login', 'POST');
        $request->merge($data);

        $response = $this->authController->login($request);
        $this->assertEquals($response->getData()->status, 'failed');
    }

    public function test_login()
    {
        $user = User::factory()->create();

        $data = [
            'email' => $user->email,
            'password' => 'password',
        ];

        $request = LoginRequest::create('/api/v1/login', 'POST');
        $request->merge($data);

        $response = $this->authController->login($request);
        $this->assertEquals($response->getData()->status, 'success');
    }

    public function test_forget_password_fail_email_not_found()
    {
        $data = [
            'email' => fake()->email(),
        ];

        $request = Request::create('/api/v1/forget-password', 'POST');
        $request->merge($data);

        $response = $this->authController->forgetPassword($request);
        $this->assertEquals($response->getData()->status, 'failed');
    }

    public function test_logout()
    {
        $user = User::factory()->create();
        $request = Request::create('/api/v1/logout', 'POST');

        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        $response = $this->authController->logout($request);

        $this->assertEquals($response->getData()->status, 'success');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
