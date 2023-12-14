<?php

namespace Database\Factories;

use App\Http\Services\WalletServices;
use App\Models\Goal;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GoalAddition>
 */
class GoalAdditionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        do {
            $userIds = User::all()->pluck('id')->toArray();
            $userId = fake()->randomElement($userIds);

            $walletIds = Wallet::where('user_id', $userId)->pluck('id')->toArray();
        } while ($walletIds == []);

        $walletId = fake()->randomElement($walletIds);
        if (! is_null($walletId)) {
            $walletBalance = app(WalletServices::class)->getBalance($walletId);
        }

        $goalIds = Goal::where('user_id', $userId)->pluck('id')->toArray();
        $goalId = fake()->randomElement($goalIds);

        $goal = Goal::find($goalId);

        $totalContributions = $goal->total_contributions;
        $differentiate = $goal->amount - $totalContributions;

        if ($differentiate > 0) {
            $positiveAmount = fake()->numberBetween(0, fake()->numberBetween(0, $walletBalance));
            $negativeAmount = fake()->numberBetween(0, $totalContributions);

            $amount = fake()->randomElement([$positiveAmount, $negativeAmount]);
        } else {
            $negativeAmount = fake()->numberBetween(0, $totalContributions);

            $amount = fake()->numberBetween($negativeAmount * -1, 0);
        }

        return [
            'amount' => $amount,
            'date' => fake()->dateTimeBetween($goal->date_begin, $goal->date_end),
            'note' => fake()->optional()->sentence,
            'goal_id' => $goalId,
            'goal_from_id' => null,
            'wallet_id' => $walletId,
        ];
    }
}
