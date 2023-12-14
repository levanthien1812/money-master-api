<?php

namespace Tests\Unit\Controllers;

use App\Http\Controllers\Api\v1\CategoriesController;
use App\Http\Requests\Categories\CreateCategoryRequest;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CategoriesControllerTest extends TestCase
{
    use RefreshDatabase;

    private $categoriesController;

    private $user;

    protected function setUp(): void
    {
        parent::setUp();
        Role::create(['name' => 'admin', 'guard_name' => 'api']);
        Role::create(['name' => 'user', 'guard_name' => 'api']);

        $this->user = User::factory()->create();
        $this->user->assignRole('user');
        $this->categoriesController = app(CategoriesController::class);

        Category::factory(10)->create();
        Category::query()->take(5)->update([
            'user_id' => $this->user->id,
            'default' => 0,
        ]);
    }

    // public function test_create()
    // {
    //

    //     $data = [
    //         'name' => fake()->word(),
    //         'image' => fake()->image(),
    //         'type' => fake()->randomElement(['incomes', 'expenses']),
    //     ];

    //     $request = CreateCategoryRequest::create('/api/v1/categories', 'POST');
    //     $request->merge($data);

    //     $response = $this->categoriesController->create($request);
    //     $this->assertTrue(true);
    // }

    public function test_get()
    {
        $request = Request::create('/api/v1/categories', 'GET');
        $request->setUserResolver(function () {
            return $this->user;
        });

        $response = $this->categoriesController->get($request);

        $this->assertEquals($response->getData()->status, 'success');
    }

    public function test_get_with_type_default()
    {

        $request = Request::create(
            '/api/v1/categories',
            'GET',
            ['type' => fake()->randomElement([
                'incomes', 'expenses',
                'default' => fake()->randomElement([0, 1]),
            ])]
        );

        $request->setUserResolver(function () {
            return $this->user;
        });

        $response = $this->categoriesController->get($request);

        $this->assertEquals($response->getData()->status, 'success');
    }

    public function test_get_default()
    {
        $inputs = [
            'type' => fake()->randomElement(['incomes', 'expenses']),
            'search' => fake()->word(),
        ];

        $request = Request::create(
            '/api/v1/categories/default',
            'GET',
            [$inputs]
        );

        $request->setUserResolver(function () {
            return $this->user;
        });

        $response = $this->categoriesController->getDefault($request);

        $this->assertEquals($response->getData()->status, 'success');
    }

    public function test_get_default_count()
    {
        $request = Request::create(
            '/api/v1/categories/default/count',
            'GET'
        );

        $request->setUserResolver(function () {
            return $this->user;
        });

        $response = $this->categoriesController->getDefaultCount($request);

        $this->assertEquals($response->getData()->status, 'success');
    }

    public function test_delete_fail_not_found()
    {
        $maxId = Category::select('id')->max('id');

        $request = Request::create(
            '/api/v1/categories',
            'DELETE'
        );

        $request->setUserResolver(function () {
            return $this->user;
        });

        $response = $this->categoriesController->delete($request, $maxId + 1);

        $this->assertEquals($response->getData()->message, 'Category not found!');
    }

    public function test_delete()
    {
        $category = Category::factory()->create();
        $category->update(['user_id' => $this->user->id]);

        $request = Request::create(
            '/api/v1/categories',
            'DELETE'
        );

        $request->setUserResolver(function () {
            return $this->user;
        });

        $response = $this->categoriesController->delete($request, $category->id);

        $this->assertEquals($response->getData()->status, 'success');
    }

    public function test_delete_default()
    {
        $category = Category::factory()->create();
        $category->update(['user_id' => null, 'default' => 1]);

        $request = Request::create(
            '/api/v1/categories/default',
            'DELETE'
        );

        $request->setUserResolver(function () {
            return $this->user;
        });

        $response = $this->categoriesController->deleteDefault($request, $category->id);

        $this->assertEquals($response->getData()->status, 'success');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
