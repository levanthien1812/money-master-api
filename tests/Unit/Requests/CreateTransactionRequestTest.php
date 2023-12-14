<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\Transactions\CreateTransactionRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class CreateTransactionRequestTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    private $createTransactionRequest;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createTransactionRequest = new CreateTransactionRequest();
    }

    public function test_authorize()
    {
        $this->assertTrue(true);
    }

    public function test_rules()
    {
        $data = [
            'title' => fake()->words(fake()->numberBetween(4, 10), true),
            'image' => fake()->image(),
            'date' => date('Y/m/d'),
            'description' => fake()->optional()->sentence,
            'user_id' => random_int(1, 100),
            'amount' => random_int(1000, 300000),
            'wallet_id' => random_int(1, 100),
            'category_id' => random_int(1, 100),
        ];

        $validator = Validator::make($data, $this->createTransactionRequest->rules());

        $this->assertTrue($validator->fails());
    }
}
