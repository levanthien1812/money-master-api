<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\Users\LoginRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class LoginRequestTest extends TestCase
{
    use RefreshDatabase;

    private $loginRequest;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loginRequest = new LoginRequest();
    }

    public function test_authorize()
    {
        $this->assertTrue(true);
    }

    public function test_rules()
    {
        $data = [
            'email' => fake()->email(),
            'password' => '12345678',
        ];

        $validator = Validator::make($data, $this->loginRequest->rules());

        $this->assertFalse($validator->fails());
    }
}
