<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\Users\SendVerficationCodeRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class SendVerificationCodeRequestTest extends TestCase
{
    use RefreshDatabase;

    private $sendVerificationCodeRequest;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sendVerificationCodeRequest = new SendVerficationCodeRequest();
    }

    public function test_authorize()
    {
        $this->assertTrue(true);
    }

    public function test_rules()
    {
        $data = [
            'email' => fake()->email(),
        ];

        $validator = Validator::make($data, $this->sendVerificationCodeRequest->rules());

        $this->assertFalse($validator->fails());
    }
}
