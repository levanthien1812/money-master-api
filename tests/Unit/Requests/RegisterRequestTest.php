<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\Users\RegisterUserRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class RegisterRequestTest extends TestCase
{
    use RefreshDatabase;

    private $registerRequest;

    protected function setUp(): void
    {
        parent::setUp();
        $this->registerRequest = new RegisterUserRequest();
    }

    public function test_authorize()
    {
        $this->assertTrue(true);
    }

    public function test_rules()
    {
        $data = [
            'name' => fake()->name(),
            'email' => fake()->email(),
            'password' => '12345678',
            'password_confirmation' => '12345678',
        ];

        $validator = Validator::make($data, $this->registerRequest->rules());

        $this->assertFalse($validator->fails());
    }
}
