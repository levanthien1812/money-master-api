<?php

namespace Tests\Unit\Controllers;

use App\Http\Controllers\Api\v1\ReportsController;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ReportsControllerTest extends TestCase
{
    use RefreshDatabase;

    private $reportsController;

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
        $this->reportsController = app(ReportsController::class);

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

    public function test_get_with_wallet_year()
    {
        $request = Request::create(
            '/api/v1/reports',
            'GET',
            [
                'wallet' => fake()->randomElement($this->wallets),
                'year' => random_int(2018, 2028),
            ]
        );

        $request->setUserResolver(function () {
            return $this->user;
        });

        $response = $this->reportsController->get($request);

        $this->assertEquals($response->getData()->status, 'success');
    }

    public function test_get_with_wallet_month_year()
    {

        $request = Request::create(
            '/api/v1/reports',
            'GET',
            [
                'wallet' => fake()->randomElement($this->wallets),
                'year' => random_int(2018, 2028),
                'month' => random_int(1, 12),
            ]
        );

        $request->setUserResolver(function () {
            return $this->user;
        });

        $response = $this->reportsController->get($request);

        $this->assertEquals($response->getData()->status, 'success');
    }

    public function test_get_with_wallet_transaction_type()
    {

        $request = Request::create(
            '/api/v1/reports',
            'GET',
            [
                'wallet' => fake()->randomElement($this->wallets),
                'transaction_type' => fake()->randomElement(['total', 'incomes', 'expenses']),
            ]
        );

        $request->setUserResolver(function () {
            return $this->user;
        });

        $response = $this->reportsController->get($request);

        $this->assertEquals($response->getData()->status, 'success');
    }

    public function test_get_with_wallet_report_type()
    {

        $request = Request::create(
            '/api/v1/reports',
            'GET',
            [
                'wallet' => fake()->randomElement($this->wallets),
                'report_type' => fake()->randomElement(['expenses-incomes', 'categories']),
            ]
        );

        $request->setUserResolver(function () {
            return $this->user;
        });

        $response = $this->reportsController->get($request);

        $this->assertEquals($response->getData()->status, 'success');
    }

    public function test_get_user_quantity()
    {
        $request = Request::create(
            '/api/v1/reports/users-per-month',
            'GET'
        );

        $request->setUserResolver(function () {
            return $this->user;
        });

        $response = $this->reportsController->getUserQuantityPerMonth($request);

        $this->assertEquals($response->getData()->status, 'success');
    }

    public function test_get_transactions_quantity()
    {
        $request = Request::create(
            '/api/v1/reports/transactions-per-month',
            'GET'
        );

        $request->setUserResolver(function () {
            return $this->user;
        });

        $response = $this->reportsController->getTransactionQuantityPerMonth($request);

        $this->assertEquals($response->getData()->status, 'success');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
