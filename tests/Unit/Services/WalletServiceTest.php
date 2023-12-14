<?php

namespace Tests\Unit\Services;

use App\Http\Services\WalletServices;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class WalletServiceTest extends TestCase
{
    use RefreshDatabase;

    private $walletService;

    private $user;

    protected function setUp(): void
    {
        parent::setUp();
        Role::create(['name' => 'admin', 'guard_name' => 'api']);
        Role::create(['name' => 'user', 'guard_name' => 'api']);

        $this->user = User::factory()->create();

        $this->user->assignRole('user');
        $this->walletService = app(WalletServices::class);
    }

    public function test_check_exists_by_name()
    {
        $existingWallet = Wallet::factory()->create();

        $resultData = $this->walletService->checkExistsByName($existingWallet->user_id, $existingWallet->name);
        $this->assertTrue($resultData);
    }

    public function test_create_fail_duplicate_name()
    {
        $existingWallet = Wallet::factory()->create();
        $existingWallet->update(['user_id' => $this->user->id]);

        $walletData = [
            'name' => $existingWallet->name,
            'image' => fake()->image(),
            'default' => fake()->randomElement([true, false]),
        ];

        $resultData = $this->walletService->create($this->user, $walletData);
        $this->assertEquals($resultData->message, 'This wallet name has been used!');
    }

    public function test_create()
    {
        do {
            $name = fake()->name();
        } while (
            $this->user->wallets()->where('name', $name)->exists()
        );

        $walletData = [
            'name' => $name,
            'image' => fake()->image(),
            'default' => fake()->randomElement([true, false]),
        ];

        $resultData = $this->walletService->create($this->user, $walletData);
        $this->assertTrue($resultData->status === 'success');
    }

    public function test_get()
    {
        $resultData = $this->walletService->get($this->user);
        $this->assertTrue($resultData->status === 'success');
    }

    public function test_get_balance()
    {
        $existingWallets = $this->user->wallets;

        if ($existingWallets->count() > 0) {
            $randomWallet = fake()->randomElement($existingWallets->toArray());
        } else {
            $randomWallet = Wallet::factory()->create();
            $randomWallet->update(['user_id' => $this->user->id]);
        }

        $resultData = $this->walletService->getBalance($randomWallet->id);

        $this->assertIsInt($resultData);
    }

    public function test_update_fail_not_found()
    {
        $maxId = Wallet::select('id')->max('id');

        $resultData = $this->walletService->update([], $maxId + 1);
        $this->assertEquals($resultData->message, 'Wallet not found!');
    }

    public function test_update()
    {
        $existingWallet = Wallet::factory()->create();

        do {
            $name = fake()->name();
        } while (
            $this->user->wallets()->where('name', $name)->exists()
        );

        $walletData = [
            'name' => $name,
            'image' => fake()->image(),
            'default' => fake()->randomElement([true, false]),
        ];

        $resultData = $this->walletService->update($walletData, $existingWallet->id);
        $this->assertTrue($resultData->status === 'success');
    }

    public function test_delete_fail_not_found()
    {
        $maxId = Wallet::select('id')->max('id');

        $resultData = $this->walletService->delete($maxId + 1);
        $this->assertEquals($resultData->message, 'Wallet not found!');
    }

    public function test_delete()
    {
        $categoryToDelete = Wallet::factory()->create();
        $categoryToDelete->update(['user_id' => $this->user->id]);

        $resultData = $this->walletService->delete($categoryToDelete->id);
        $this->assertTrue($resultData->status === 'success');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
