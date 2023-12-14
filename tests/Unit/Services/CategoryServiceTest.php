<?php

namespace Tests\Unit\Services;

use App\Http\Services\CategoryServices;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CategoryServiceTest extends TestCase
{
    use RefreshDatabase;

    private $categoryService;

    private $user;

    protected function setUp(): void
    {
        parent::setUp();
        Role::create(['name' => 'admin', 'guard_name' => 'api']);
        Role::create(['name' => 'user', 'guard_name' => 'api']);

        $this->user = User::factory()->create();
        $this->user->assignRole('user');
        $this->categoryService = app(CategoryServices::class);
    }

    public function test_check_exists_by_name()
    {
        do {
            $existingCategory = Category::factory()->create();
        } while ($existingCategory->default == 1);

        $resultData = $this->categoryService->checkExists($existingCategory->user_id, $existingCategory->name);
        $this->assertTrue($resultData);
    }

    public function test_create_fail_invalid_type()
    {
        $categoryData = [
            'name' => fake()->name(),
            'image' => fake()->image(),
            'type' => 'lfslakfjk',
        ];

        $resultData = $this->categoryService->create($this->user, $categoryData);
        $this->assertEquals($resultData->message, 'Invalid type');
    }

    public function test_create_fail_duplicate_name()
    {
        $existingCategory = Category::factory()->create();
        $existingCategory->update(['user_id' => $this->user->id]);

        $categoryData = [
            'name' => $existingCategory->name,
            'image' => fake()->image(),
            'type' => fake()->randomElement(['expenses', 'incomes']),
        ];

        $resultData = $this->categoryService->create($this->user, $categoryData);
        $this->assertEquals($resultData->message, 'Category name has been used');
    }

    public function test_create()
    {
        do {
            $name = fake()->name();
        } while (
            $this->user->categories()->where('name', $name)->exists()
            || Category::where(['default' => 1, 'name' => $name])->exists()
        );

        $categoryData = [
            'name' => $name,
            'image' => fake()->image(),
            'type' => fake()->randomElement(['expenses', 'incomes']),
        ];

        $resultData = $this->categoryService->create($this->user, $categoryData);
        $this->assertTrue($resultData->status === 'success');
    }

    public function test_get_without_plan_and_ignore()
    {
        $inputs = [
            'type' => fake()->randomElement(['expenses', 'incomes']),
            'default' => fake()->randomElement([true, false]),
        ];

        $resultData = $this->categoryService->get($this->user, $inputs);

        $this->assertTrue($resultData->status === 'success');
    }

    public function test_get_with_ignore()
    {
        $inputs = [
            'type' => fake()->randomElement(['expenses', 'incomes']),
            'default' => fake()->randomElement([true, false]),
            'ignore_exists' => true,
            'month' => random_int(1, 12),
            'year' => random_int(date('Y') - 2, date('Y') + 2),
        ];

        $resultData = $this->categoryService->get($this->user, $inputs);

        $this->assertTrue($resultData->status === 'success');
    }

    public function test_get_default()
    {
        $inputs = [
            'type' => fake()->randomElement(['expenses', 'incomes']),
            'search' => Str::random(random_int(1, 10)),
        ];

        $resultData = $this->categoryService->getDefault($inputs);

        $this->assertTrue($resultData->status === 'success');
    }

    public function test_get_default_count()
    {
        $resultData = $this->categoryService->getDefaultCount();
        $this->assertTrue($resultData->status === 'success');
    }

    public function test_update_fail_not_found()
    {
        $maxId = Category::select('id')->max('id');

        $resultData = $this->categoryService->update([], $maxId + 1);
        $this->assertEquals($resultData->message, 'Category not found!');
    }

    public function test_update()
    {
        $existingCategory = Category::factory()->create();

        do {
            $name = fake()->name();
        } while (
            $this->user->categories()->where('name', $name)->exists()
            || Category::where(['default' => 1, 'name' => $name])->exists()
        );

        $categoryData = [
            'name' => $name,
            'image' => fake()->image(),
            'type' => fake()->randomElement(['expenses', 'incomes']),
        ];

        $resultData = $this->categoryService->update($categoryData, $existingCategory->id);
        $this->assertTrue($resultData->status === 'success');
    }

    public function test_delete_fail_not_found()
    {
        $maxId = Category::select('id')->max('id');

        $resultData = $this->categoryService->delete($maxId + 1);
        $this->assertEquals($resultData->message, 'Category not found!');
    }

    public function test_delete()
    {
        $categoryToDelete = Category::factory()->create();

        $resultData = $this->categoryService->delete($categoryToDelete->id);
        $this->assertTrue($resultData->status === 'success');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
