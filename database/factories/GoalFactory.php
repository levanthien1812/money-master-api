<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Goal>
 */
class GoalFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $userIds = User::all()->pluck('id')->toArray();
        $userId = fake()->randomElement($userIds);

        $dateBegin = fake()->dateTimeBetween('-1 year', '+1 year');
        $dateEnd = fake()->dateTimeInInterval($dateBegin, '+'.random_int(1, 1000).' days');

        $types = config('goal.goaltypes');

        return [
            'name' => fake()->words(fake()->numberBetween(4, 10), true),
            'type' => fake()->randomElement($types),
            'image' => fake()->randomElement([env('APP_URL').'/images/samples/goals/goal-'.random_int(1, 8).'.png', '']),
            'date_begin' => $dateBegin,
            'date_end' => $dateEnd,
            'description' => fake()->optional()->sentence,
            'user_id' => $userId,
            'amount' => fake()->numberBetween(1000, 3000000),
            'is_important' => random_int(0, 1),
        ];
    }
}
