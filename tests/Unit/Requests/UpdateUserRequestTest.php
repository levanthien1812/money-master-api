<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\Users\UpdateUserRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class UpdateUserRequestTest extends TestCase
{
    use RefreshDatabase;

    private $updateUserRequest;

    protected function setUp(): void
    {
        parent::setUp();
        $this->updateUserRequest = new UpdateUserRequest();
    }

    public function test_authorize()
    {
        $this->assertTrue(true);
    }

    public function test_rules()
    {
        $data = [
            'photo' => fake()->image(),
            'name' => fake()->name(),
            'email' => fake()->email(),
        ];

        $validator = Validator::make($data, $this->updateUserRequest->rules());

        $this->assertTrue($validator->fails());
    }
}
