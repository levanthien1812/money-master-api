<?php

namespace Tests\Unit\Services;

use App\Http\Services\ReportService;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ReportServiceTest extends TestCase
{
    use RefreshDatabase;

    private $reportService;

    private $user;

    protected function setUp(): void
    {
        parent::setUp();
        Role::create(['name' => 'admin', 'guard_name' => 'api']);
        Role::create(['name' => 'user', 'guard_name' => 'api']);

        $this->user = User::factory()->create();

        $this->user->assignRole('user');
        $this->reportService = app(ReportService::class);
    }

    public function test_get()
    {
        if ($this->user->wallets()->count() > 0) {
            $walletId = $this->user->wallets()->first()->id;
        } else {
            $wallet = Wallet::factory()->create();
            $wallet->update(['user_id' => $this->user->id]);

            $walletId = $wallet->id;
        }

        $inputs = [
            'month' => random_int(1, 12),
            'year' => random_int(date('Y') - 3, date('Y') + 3),
            'wallet' => $walletId,
            'transaction_type' => fake()->randomElement(['total', 'incomes', 'expenses']),
            'report_type' => fake()->randomElement(['expenses-incomes', 'categories']),
        ];

        $resultData = $this->reportService->get($this->user, $inputs);
        $this->assertTrue($resultData->status === 'success');
    }

    public function test_get_user_quantity_per_month()
    {
        $inputs = [
            'year' => random_int(date('Y') - 3, date('Y') + 3),
        ];

        $resultData = $this->reportService->getUserQuantityPerMonth($inputs);
        $this->assertTrue($resultData->status === 'success');
    }

    public function test_get_transaction_quantity_per_month()
    {
        $inputs = [
            'year' => random_int(date('Y') - 3, date('Y') + 3),
        ];

        $resultData = $this->reportService->getTransactionQuantityPerMonth($inputs);
        $this->assertTrue($resultData->status === 'success');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
