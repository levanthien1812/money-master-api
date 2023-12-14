<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\Users\ResetPasswordRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Tests\TestCase;

class ResetPasswordRequestTest extends TestCase
{
    use RefreshDatabase;

    private $resetPasswordRequest;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resetPasswordRequest = new ResetPasswordRequest();
    }

    public function test_authorize()
    {
        $this->assertTrue(true);
    }

    public function test_rules()
    {
        $data = [
            'newPassword' => '12345678',
            'newPassword_confirmation' => '12345678',
            'token' => Str::random(30),
            'email' => fake()->email(),
        ];

        $validator = Validator::make($data, $this->resetPasswordRequest->rules());

        $this->assertFalse($validator->fails());
    }
}
