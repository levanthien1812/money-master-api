<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MonthPlan>
 */
class MonthPlanFactory extends Factory
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
        } while ($walletIds == []);

        $walletId = fake()->randomElement($walletIds);
        $month = fake()->numberBetween(1, 12);
        $year = fake()->numberBetween(2020, 2026);

        return [
            'month' => $month,
            'year' => $year,
            'amount' => fake()->numberBetween(1000000, 10000000),
            'user_id' => $userId,
            'wallet_id' => $walletId,
        ];
    }
}
