<?php

namespace App\Http\Services;

use App\Http\Helpers\FailedData;
use App\Http\Helpers\SuccessfulData;
use App\Models\GoalAddition;
use Carbon\Carbon;
use Exception;

class GoalAdditionService extends BaseService
{
    public function __construct()
    {
        parent::__construct(GoalAddition::class);
    }

    public function create(int $goalId, array $data): object
    {
        try {
            $goal = app(GoalService::class)->getById($goalId);
            if (! $goal) {
                return new FailedData('Goal not found!');
            }

            $wallet = app(WalletServices::class)->getById($data['wallet_id']);
            if (! $wallet) {
                return new FailedData('Wallet not found!');
            }

            $totalContributions = app(GoalService::class)->getTotalContributions($goal->id);
            if ($data['amount'] > 0 && $totalContributions >= $goal->amount) {
                return new FailedData('Can not add to goal as goal is finished!');
            }

            if ($data['amount'] < 0 && abs($data['amount']) > $totalContributions) {
                $message = "Goal's total contributions (".$totalContributions.') is not enough to perform this withdrawal!';

                return new FailedData($message, ['amount' => $message]);
            }

            $walletBalance = app(WalletServices::class)->getBalance($wallet->id);

            if ($data['amount'] > $walletBalance) {
                $message = "Wallet's balance (".$walletBalance.') is not enough to perform this addition!';

                return new FailedData($message, ['amount' => $message]);
            }

            $goalAdditionData = array_merge($data, ['date' => Carbon::today(), 'goal_id' => $goalId]);

            $newGoalAddition = $this->createGoalAddition($goalAdditionData);

            return new SuccessfulData('Create goal addition successfully!', ['goal_addition' => $newGoalAddition]);
        } catch (Exception $error) {
            return new FailedData('Failed to create goal addition!', ['error' => $error]);
        }
    }

    public function getByGoalId(int $goalId): object
    {
        try {
            $goal = app(GoalService::class)->getById($goalId);

            if (! $goal) {
                return new FailedData('Goal not found!');
            }

            $additions = $goal->goal_additions()->with('wallet')->with('goal_from')->get();

            return new SuccessfulData('Get goal additions successfully', ['goal_additions' => $additions]);
        } catch (Exception $error) {
            return new FailedData('Failed to get goal additions!', ['error' => $error]);
        }
    }

    public function createGoalAddition($data): ?GoalAddition
    {
        return $this->model::create($data);
    }

    public function deleteByGoalId($goalId): void
    {
        $this->model::where('goal_id', $goalId)->orWhere('goal_from_id', $goalId)->delete();
    }
}
