<?php

namespace Tests\Unit\Controllers;

use App\Http\Controllers\Api\v1\TransactionsController;
use App\Http\Requests\Transactions\CreateTransactionRequest as TransactionsCreateTransactionRequest;
use App\Http\Requests\Transactions\UpdateTransactionRequest;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TransactionsControllerTest extends TestCase
{
    use RefreshDatabase;

    private $transactionsController;

    private $user;

    private $wallets;

    private $categories;

    protected function setUp(): void
    {
        parent::setUp();
        Role::create(['name' => 'admin', 'guard_name' => 'api']);
        Role::create(['name' => 'user', 'guard_name' => 'api']);

        $this->user = User::factory()->create();
        $this->user->assignRole('user');
        $this->transactionsController = app(TransactionsController::class);

        Wallet::factory(3)->create();
        Wallet::query()->update(['user_id' => $this->user->id]);
        $this->wallets = Wallet::where('user_id', $this->user->id)->get()->pluck('id');

        Category::factory(10)->create();
        Category::query()->update(['user_id' => $this->user->id]);
        $this->categories = Category::where('user_id', $this->user->id)->get()->pluck('id');

        Transaction::factory(30)->create();
        Transaction::take(10)->update([
            'user_id' => $this->user->id,
            'wallet_id' => fake()->randomElement($this->wallets),
            'category_id' => fake()->randomElement($this->categories),
        ]);
    }

    public function test_create()
    {
        $data = [
            'title' => fake()->words(fake()->numberBetween(4, 10), true),
            'image' => fake()->optional()->image(),
            'date' => date('Y/m/d'),
            'description' => fake()->optional()->sentence,
            'amount' => fake()->numberBetween(1000, 300000),
            'wallet_id' => fake()->randomElement($this->wallets),
            'category_id' => fake()->randomElement($this->categories),
        ];

        $request = TransactionsCreateTransactionRequest::create('/api/v1/transactions', 'POST');
        $request->merge($data);
        $request->setUserResolver(function () {
            return $this->user;
        });

        $response = $this->transactionsController->create($request);
        $this->assertEquals($response->getData()->status, 'success');
    }

    public function test_get_with_wallet()
    {
        $request = Request::create(
            '/api/v1/transactions',
            'GET',
            ['wallet' => fake()->randomElement($this->wallets)]
        );

        $request->setUserResolver(function () {
            return $this->user;
        });

        $response = $this->transactionsController->get($request);

        $this->assertEquals($response->getData()->status, 'success');
    }

    public function test_get_with_categories()
    {

        $request = Request::create(
            '/api/v1/transactions',
            'GET',
            ['category' => fake()->randomElement($this->categories)]
        );

        $request->setUserResolver(function () {
            return $this->user;
        });

        $response = $this->transactionsController->get($request);

        $this->assertEquals($response->getData()->status, 'success');
    }

    public function test_get_with_transaction_type()
    {

        $request = Request::create(
            '/api/v1/transactions',
            'GET',
            ['transaction_type' => fake()->randomElement(['total', 'incomes', 'expenses'])]
        );

        $request->setUserResolver(function () {
            return $this->user;
        });

        $response = $this->transactionsController->get($request);

        $this->assertEquals($response->getData()->status, 'success');
    }

    public function test_get_with_day_month_year()
    {
        $request = Request::create(
            '/api/v1/transactions',
            'GET',
            [
                'day' => random_int(0, 30),
                'month' => random_int(1, 12),
                'year' => random_int(2018, 2028),
            ]
        );

        $request->setUserResolver(function () {
            return $this->user;
        });

        $response = $this->transactionsController->get($request);

        $this->assertEquals($response->getData()->status, 'success');
    }

    public function test_get_count()
    {
        $request = Request::create(
            '/api/v1/transactions/count',
            'GET'
        );

        $request->setUserResolver(function () {
            return $this->user;
        });

        $response = $this->transactionsController->getCounts($request);

        $this->assertEquals($response->getData()->status, 'success');
    }

    public function test_get_years()
    {
        $request = Request::create(
            '/api/v1/transactions/years',
            'GET'
        );

        $request->setUserResolver(function () {
            return $this->user;
        });

        $response = $this->transactionsController->getYears($request);

        $this->assertEquals($response->getData()->status, 'success');
    }

    public function test_delete()
    {
        $transaction = Transaction::factory()->create();
        $transaction->update(['user_id' => $this->user->id]);

        $request = Request::create(
            '/api/v1/transactions',
            'DELETE'
        );

        $request->setUserResolver(function () {
            return $this->user;
        });

        $response = $this->transactionsController->delete($request, $transaction->id);

        $this->assertEquals($response->getData()->status, 'success');
    }

    public function test_update()
    {
        $transaction = Transaction::where('user_id', $this->user->id)->first();

        $data = [
            'title' => fake()->words(fake()->numberBetween(4, 10), true),
            'image' => fake()->image(),
            'date' => date('Y/m/d'),
            'description' => fake()->optional()->sentence,
            'amount' => fake()->numberBetween(1000, 300000),
            'wallet_id' => fake()->randomElement($this->wallets),
            'category_id' => fake()->randomElement($this->categories),
        ];

        $request = UpdateTransactionRequest::create('/api/v1/transactions', 'PATCH');
        $request->merge($data);
        $request->setUserResolver(function () {
            return $this->user;
        });

        $response = $this->transactionsController->update($request, $transaction->id);
        $this->assertEquals($response->getData()->status, 'success');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
