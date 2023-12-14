<?php

namespace Tests\Unit\Services;

use App\Http\Services\TransactionServices;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TransactionServiceTest extends TestCase
{
    use RefreshDatabase;

    private $transactionService;

    private $user;

    protected function setUp(): void
    {
        parent::setUp();
        Role::create(['name' => 'admin', 'guard_name' => 'api']);
        Role::create(['name' => 'user', 'guard_name' => 'api']);

        $this->user = User::factory()->create();
        $this->user->assignRole('user');
        $this->transactionService = app(TransactionServices::class);
    }

    public function test_create_fail_category_not_found()
    {
        $maxId = Category::select('id')->max('id');

        $transactionData = ['category_id' => $maxId + 1];

        $resultData = $this->transactionService->create($this->user, $transactionData);
        $this->assertEquals($resultData->message, 'Category not found!');
    }

    public function test_create_fail_wallet_not_found()
    {
        $category = Category::factory()->create();
        $category->update(['user_id' => $this->user->id]);

        $maxId = Wallet::select('id')->max('id');

        $transactionData = [
            'category_id' => $category->id,
            'wallet_id' => $maxId + 1,
        ];

        $resultData = $this->transactionService->create($this->user, $transactionData);
        $this->assertEquals($resultData->message, 'Wallet not found!');
    }

    public function test_create()
    {
        $existingWallets = $this->user->wallets;
        $existingCategories = $this->user->categories;

        if ($existingWallets->count() > 0) {
            $randomWallet = fake()->randomElement($existingWallets->toArray());
        } else {
            $randomWallet = Wallet::factory()->create();
            $randomWallet->update(['user_id' => $this->user->id]);
        }

        if ($existingCategories->count() > 0) {
            $randomCategory = fake()->randomElement($existingCategories->toArray());
        } else {
            $randomCategory = Category::factory()->create();
            $randomCategory->update(['user_id' => $this->user->id]);
        }

        $transactionData = [
            'title' => fake()->words(fake()->numberBetween(4, 10), true),
            'image' => fake()->image(),
            'date' => fake()->dateTimeBetween('-1 year', 'today'),
            'description' => fake()->optional()->sentence,
            'amount' => fake()->numberBetween(1000, 300000),
            'wallet_id' => $randomWallet->id,
            'category_id' => $randomCategory->id,
        ];

        $resultData = $this->transactionService->create($this->user, $transactionData);
        $this->assertTrue($resultData->status === 'success');
    }

    public function test_get_without_plan_and_ignore()
    {
        $inputs = [
            'type' => fake()->randomElement(['expenses', 'incomes']),
            'default' => fake()->randomElement([true, false]),
        ];

        $resultData = $this->transactionService->get($this->user, $inputs);

        $this->assertTrue($resultData->status === 'success');
    }

    public function test_update_fail_not_found()
    {
        $maxId = Transaction::select('id')->max('id');

        $resultData = $this->transactionService->update($this->user, [], $maxId + 1);
        $this->assertEquals($resultData->message, 'Transaction not found!');
    }

    private function get_sample_transaction()
    {
        $wallet = Wallet::factory()->create();
        $wallet->update(['user_id' => $this->user->id]);

        $category = Category::factory()->create();
        $category->update(['user_id' => $this->user->id]);

        $transaction = Transaction::create([
            'title' => fake()->words(fake()->numberBetween(4, 10), true),
            'date' => Carbon::now(),
            'user_id' => $this->user->id,
            'amount' => fake()->numberBetween(1000, 300000),
            'wallet_id' => $wallet->id,
            'category_id' => $category->id,
        ]);

        return $transaction;
    }

    public function test_update_fail_category_not_found()
    {
        $transaction = $this->get_sample_transaction();

        $maxId = Category::select('id')->max('id');

        $resultData = $this->transactionService->update($this->user, ['category_id' => $maxId + 1], $transaction->id);
        $this->assertEquals($resultData->message, 'Category not found!');
    }

    public function test_update_fail_wallet_not_found()
    {
        $transaction = $this->get_sample_transaction();

        $maxId = Wallet::select('id')->max('id');

        $resultData = $this->transactionService->update($this->user, [
            'wallet_id' => $maxId + 1,
        ], $transaction->id);

        $this->assertEquals($resultData->message, 'Wallet not found!');
    }

    public function test_update()
    {
        $transaction = $this->get_sample_transaction();

        $data = [
            'amount' => fake()->randomFloat(0, 1000, 1000000),
            'title' => fake()->sentences(),
            'image' => fake()->image(),
            'date' => fake()->date(),
        ];

        $resultData = $this->transactionService->update($this->user, $data, $transaction->id);
        $this->assertTrue($resultData->status === 'success');
    }

    public function test_delete_fail_not_found()
    {
        $maxId = Transaction::select('id')->max('id');

        $resultData = $this->transactionService->delete($maxId + 1);
        $this->assertEquals($resultData->message, 'Transaction not found!');
    }

    public function test_delete()
    {
        $transaction = $this->get_sample_transaction();

        $resultData = $this->transactionService->delete($transaction->id);
        $this->assertTrue($resultData->status === 'success');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
