<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->word;
        $image = env('APP_URL') . '/images/samples/categories/category-' . random_int(1, 6) . '.jpg';
        $default = fake()->randomElement([0, 1]);

        $types = config('category.categorytypes');
        $type = fake()->randomElement($types);

        $userIds = User::pluck('id')->toArray();
        $randomUserId = fake()->randomElement($userIds);

        $userId = ($default === 0) ? $randomUserId : null;

        return [
            'name' => $name,
            'image' => $image,
            'type' => $type,
            'user_id' => $userId,
            'default' => $default,
        ];
    }
}
