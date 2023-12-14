<?php

namespace Tests\Unit\Services;

use App\Http\Services\GoalService;
use App\Models\Goal;
use App\Models\GoalAddition;
use App\Models\User;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GoalServiceTest extends TestCase
{
    use RefreshDatabase;

    private $goalService;

    private $user;

    protected function setUp(): void
    {
        parent::setUp();
        Role::create(['name' => 'admin', 'guard_name' => 'api']);
        Role::create(['name' => 'user', 'guard_name' => 'api']);

        $this->user = User::factory()->create();
        $this->user->assignRole('user');
        $this->goalService = app(GoalService::class);

        Goal::factory(8)->create();
        Wallet::factory(3)->create();
        GoalAddition::factory(200)->create();
    }

    public function test_create_fail_name_exists(): void
    {
        $existsingGoal = Goal::factory()->create();

        $resultData = $this->goalService->create($this->user, ['name' => $existsingGoal->name]);

        $this->assertEquals($resultData->message, 'Goal with this name is already exists!');
    }

    public function test_create_fail_invalid_date_begin(): void
    {
        $resultData = $this->goalService->create(
            $this->user,
            ['name' => 'skdflakfdjlk', 'date_begin' => Carbon::yesterday()]
        );

        $this->assertEquals($resultData->message, 'Goal\'s begining date must be from today!');
    }

    public function test_create_fail_invalid_date_begin_date_end(): void
    {
        $resultData = $this->goalService->create(
            $this->user,
            [
                'name' => 'skdflakfdjlk',
                'date_begin' => Carbon::today(),
                'date_end' => Carbon::yesterday(),
            ]
        );

        $this->assertEquals($resultData->message, 'Goal\'s ending date must be after begining date!');
    }

    public function test_create(): void
    {
        $data = [
            'name' => fake()->words(fake()->numberBetween(4, 10), true),
            'type' => fake()->randomElement(['saving', 'debt-reduction']),
            'image' => fake()->image(),
            'date_begin' => Carbon::today(),
            'date_end' => Carbon::tomorrow(),
            'description' => fake()->optional()->sentence,
            'amount' => fake()->numberBetween(1000, 3000000),
            'is_important' => random_int(0, 1),
        ];

        $resultData = $this->goalService->create($this->user, $data);

        $this->assertEquals($resultData->status, 'success');
    }

    public function test_get_fail_invalid_type(): void
    {
        $inputs = [
            'type' => 'an-invalid-type',
        ];

        $resultData = $this->goalService->get($this->user, $inputs);

        $this->assertEquals(
            $resultData->message,
            'Invalid goal type'
        );
    }

    public function test_get_fail_invalid_status(): void
    {
        $inputs = [
            'status' => 'an-invalid-status',
        ];

        $resultData = $this->goalService->get($this->user, $inputs);

        $this->assertEquals(
            $resultData->message,
            'Invalid goal status'
        );
    }

    public function test_get(): void
    {
        $inputs = [
            'type' => fake()->optional()->randomElement(['saving', 'debt-reduction']),
            'status' => fake()->randomElement(['not-started', 'in-progress', 'finished', 'not-completed']),
            'search' => fake()->optional()->name(),
        ];

        $resultData = $this->goalService->get($this->user, $inputs);

        $this->assertEquals($resultData->status, 'success');
    }

    public function test_get_transferable(): void
    {
        $inputs = [
            'transfer_amount' => random_int(1, 300000),
        ];

        $resultData = $this->goalService->getTransferable($this->user, $inputs);

        $this->assertEquals($resultData->status, 'success');
    }

    public function test_transfer_to_another_goal_fail_not_enough_money(): void
    {

        $goalIds = Goal::withSum('goal_additions', 'amount')
            ->where('amount', '>', 'goal_additions_sum_amount')
            ->get()->pluck('id')->toArray();

        if (count($goalIds) === 0) {
            $this->assertTrue(true);
        }

        $goalId = fake()->randomElement($goalIds);

        do {
            $goalToId = fake()->randomElement($goalIds);
        } while ($goalToId == $goalId);

        $data = [
            'goal_to_id' => $goalToId,
        ];

        $resultData = $this->goalService->transferToAnotherGoal($goalId, $data);

        $this->assertEquals($resultData->message, 'Not enough money to transfer!');
    }

    public function test_get_total_contribution(): void
    {
        $goalIds = Goal::all()->pluck('id')->toArray();
        $goalId = fake()->randomElement($goalIds);

        $resultData = $this->goalService->getTotalContributions($goalId);

        $this->assertIsInt($resultData);
    }

    public function test_return_to_wallet_fail_not_enough_money(): void
    {
        $goalIds = Goal::withSum('goal_additions', 'amount')
            ->where('amount', '>', 'goal_additions_sum_amount')
            ->get()->pluck('id')->toArray();

        if (count($goalIds) === 0) {
            $this->assertTrue(true);
        }

        $goalId = fake()->randomElement($goalIds);

        $resultData = $this->goalService->returnBackToWallet($goalId, []);

        $this->assertEquals($resultData->message, 'Not enough money to transfer!');
    }

    public function test_return_to_wallet()
    {
        $goalIds = Goal::withSum('goal_additions', 'amount')
            ->where('amount', '<', 'goal_additions_sum_amount')
            ->get()->pluck('id')->toArray();

        if (count($goalIds) === 0) {
            return $this->assertTrue(true);
        }

        $goalId = fake()->randomElement($goalIds);

        $walletIds = Wallet::all()->pluck('id')->toArray();
        $walletId = fake()->randomElement($walletIds);

        $resultData = $this->goalService->returnBackToWallet($goalId, ['wallet_id' => $walletId]);

        $this->assertEquals($resultData->status, 'success');
    }
}
