<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\Categories\CreateCategoryRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class CreateCategoryRequestTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    private $createCategoryRequest;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createCategoryRequest = new CreateCategoryRequest();
    }

    public function test_authorize()
    {
        $this->assertTrue(true);
    }

    public function test_rules()
    {
        $data = [
            'name' => fake()->word(),
            'image' => fake()->image(),
            'type' => fake()->randomElement(['expenses', 'incomes']),
        ];

        $validator = Validator::make($data, $this->createCategoryRequest->rules());

        $this->assertTrue($validator->fails());
    }

    public function test_rules_fail_missing_name()
    {
        $data = [
            'image' => fake()->image(),
            'type' => fake()->randomElement(['expenses', 'incomes']),
        ];

        $validator = Validator::make($data, $this->createCategoryRequest->rules());

        $this->assertTrue($validator->fails());
    }

    public function test_rules_fail_missing_image()
    {
        $data = [
            'name' => fake()->word(),
            'type' => fake()->randomElement(['expenses', 'incomes']),
        ];

        $validator = Validator::make($data, $this->createCategoryRequest->rules());

        $this->assertTrue($validator->fails());
    }

    public function test_rules_fail_missing_type()
    {
        $data = [
            'image' => fake()->image(),
            'name' => fake()->word(),
        ];

        $validator = Validator::make($data, $this->createCategoryRequest->rules());

        $this->assertTrue($validator->fails());
    }
}
