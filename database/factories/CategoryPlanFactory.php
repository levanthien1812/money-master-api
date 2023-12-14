<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CategoryPlan>
 */
class CategoryPlanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        do {
            $userIds = User::take(10)->pluck('id')->toArray();
            $userId = fake()->randomElement($userIds);

            $walletIds = Wallet::where('user_id', $userId)->pluck('id')->toArray();
            $categoryIds = Category::where('user_id', $userId)->pluck('id')->toArray();
        } while ($walletIds == [] || $categoryIds == []);

        $walletId = fake()->randomElement($walletIds);
        $categoryId = fake()->randomElement($categoryIds);

        $month = fake()->numberBetween(1, 12);
        $year = fake()->numberBetween(2020, 2026);

        return [
            'month' => $month,
            'year' => $year,
            'amount' => fake()->numberBetween(50000, 3000000),
            'user_id' => $userId,
            'wallet_id' => $walletId,
            'category_id' => $categoryId,
        ];
    }
}
