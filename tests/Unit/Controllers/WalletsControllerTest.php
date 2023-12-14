<?php

namespace Tests\Unit\Controllers;

use App\Http\Controllers\Api\v1\WalletsController;
use App\Http\Requests\Wallets\CreateWalletRequest;
use App\Http\Requests\Wallets\UpdateWalletRequest;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class WalletsControllerTest extends TestCase
{
    use RefreshDatabase;

    private $walletsController;

    private $user;

    protected function setUp(): void
    {
        parent::setUp();
        Role::create(['name' => 'admin', 'guard_name' => 'api']);
        Role::create(['name' => 'user', 'guard_name' => 'api']);

        $this->user = User::factory()->create();
        $this->user->assignRole('user');
        $this->walletsController = app(WalletsController::class);
    }

    public function test_create()
    {
        $data = [
            'name' => fake()->word(),
            'image' => fake()->image(),
            'default' => fake()->randomElement([0, 1]),
        ];

        $request = CreateWalletRequest::create('/api/v1/wallets', 'POST');
        $request->merge($data);
        $request->setUserResolver(function () {
            return $this->user;
        });

        $response = $this->walletsController->create($request);
        $this->assertEquals($response->getData()->status, 'success');
    }

    public function test_create_fail_duplicate_name()
    {
        $wallet = Wallet::factory()->create();
        $wallet->update(['user_id' => $this->user->id]);

        $request = CreateWalletRequest::create('/api/v1/wallets', 'POST');
        $request->merge(['name' => $wallet->name]);
        $request->setUserResolver(function () {
            return $this->user;
        });

        $response = $this->walletsController->create($request);
        $this->assertEquals($response->getData()->message, 'This wallet name has been used!');
    }

    public function test_get()
    {
        $request = Request::create('/api/v1/wallets', 'GET');
        $request->setUserResolver(function () {
            return $this->user;
        });

        $response = $this->walletsController->get($request);

        $this->assertEquals($response->getData()->status, 'success');
    }

    public function test_update()
    {
        $wallet = Wallet::factory()->create();
        $wallet->update(['user_id' => $this->user->id]);

        $data = [
            'name' => fake()->word(),
            'image' => fake()->image(),
            'default' => fake()->randomElement([0, 1]),
        ];

        $request = UpdateWalletRequest::create('/api/v1/wallets', 'PATCH');
        $request->merge($data);
        $request->setUserResolver(function () {
            return $this->user;
        });

        $response = $this->walletsController->update($request, $wallet->id);
        $this->assertEquals($response->getData()->status, 'success');
    }

    public function test_delete()
    {
        $wallet = Wallet::factory()->create();
        $wallet->update(['user_id' => $this->user->id]);

        $request = Request::create(
            '/api/v1/wallets',
            'DELETE'
        );

        $request->setUserResolver(function () {
            return $this->user;
        });

        $response = $this->walletsController->delete($request, $wallet->id);

        $this->assertEquals($response->getData()->status, 'success');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
