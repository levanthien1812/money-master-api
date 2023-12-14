<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\Categories\UpdateCategoryRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class UpdateCategoryRequestTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    private $updateCategoryRequest;

    protected function setUp(): void
    {
        parent::setUp();
        $this->updateCategoryRequest = new UpdateCategoryRequest();
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

        $validator = Validator::make($data, $this->updateCategoryRequest->rules());

        $this->assertTrue($validator->fails());
    }

    public function test_rules_without_name()
    {
        $data = [
            'image' => fake()->image(),
            'type' => fake()->randomElement(['expenses', 'incomes']),
        ];

        $validator = Validator::make($data, $this->updateCategoryRequest->rules());

        $this->assertTrue($validator->fails());
    }

    public function test_rules_without_image()
    {
        $data = [
            'name' => fake()->word(),
            'type' => fake()->randomElement(['expenses', 'incomes']),
        ];

        $validator = Validator::make($data, $this->updateCategoryRequest->rules());

        $this->assertFalse($validator->fails());
    }

    public function test_rules_without_type()
    {
        $data = [
            'image' => fake()->image(),
            'name' => fake()->word(),
        ];

        $validator = Validator::make($data, $this->updateCategoryRequest->rules());

        $this->assertTrue($validator->fails());
    }
}
