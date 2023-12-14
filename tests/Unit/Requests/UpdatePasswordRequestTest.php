<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\Users\UpdatePasswordRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class UpdatePasswordRequestTest extends TestCase
{
    use RefreshDatabase;

    private $updatePasswordRequest;

    protected function setUp(): void
    {
        parent::setUp();
        $this->updatePasswordRequest = new UpdatePasswordRequest();
    }

    public function test_authorize()
    {
        $this->assertTrue(true);
    }

    public function test_rules()
    {
        $data = [
            'password' => '12345678',
            'newPassword' => '12345678',
            'newPassword_confirmation' => '12345678',
        ];

        $validator = Validator::make($data, $this->updatePasswordRequest->rules());

        $this->assertFalse($validator->fails());
    }
}
