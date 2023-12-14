<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
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

        $categories = Category::where('user_id', $userId)->orWhere('default', 1)->get()->toArray();
        $randomCategory = fake()->randomElement($categories);

        if ($randomCategory['default'] == 1) {
            $newCategory = Category::updateOrCreate([
                'name' => $randomCategory['name'],
                'type' => $randomCategory['type'],
                'image' => $randomCategory['image'],
                'user_id' => $userId,
                'default' => 0,
            ], []);
        }

        if (isset($newCategory)) {
            $categoryId = $newCategory->id;
        } else {
            $categoryId = $randomCategory['id'];
        }

        $date = fake()->dateTimeBetween('-1 year', 'today');

        return [
            'title' => fake()->words(fake()->numberBetween(4, 10), true),
            'image' => fake()->randomElement([env('APP_URL').'/images/samples/transactions/transaction-'.random_int(1, 1).'.jpg', '']),
            'date' => $date,
            'description' => fake()->optional()->sentence,
            'user_id' => $userId,
            'amount' => fake()->numberBetween(1000, 300000),
            'wallet_id' => $walletId,
            'category_id' => $categoryId,
        ];
    }
}
