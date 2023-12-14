<?php

namespace App\Http\Services;

use App\Http\Helpers\FailedData;
use App\Http\Helpers\StorageHelper;
use App\Http\Helpers\SuccessfulData;
use App\Models\Goal;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Str;

class GoalService extends BaseService
{
    public function __construct()
    {
        parent::__construct(Goal::class);
    }

    public function create(User $user, array $data): object
    {
        try {
            $goalData = array_merge($data, ['user_id' => $user->id]);

            $types = config('goal.goaltypes');

            if (!in_array($data['type'], $types)) {
                return new FailedData('Invalid type', ['type' => 'Type is invalid! Available types: ' . implode(' / ', $types)]);
            }

            if ($this->checkExistsByName($goalData)) {
                $message = 'Goal with this name is already exists!';

                return new FailedData($message, ['name' => $message]);
            }

            if (Carbon::parse($data['date_begin'])->lt(Carbon::today())) {
                $message = 'Goal\'s begining date must be from today!';

                return new FailedData($message, ['date_begin' => $message]);
            }

            if (Carbon::parse($data['date_begin'])->gt(Carbon::parse($data['date_end']))) {
                $message = 'Goal\'s ending date must be after begining date!';

                return new FailedData($message, ['date_end' => $message]);
            }

            $image = isset($data['image']) ? $data['image'] : null;
            if ($image) {
                $imageUrl = StorageHelper::store($image, '/public/images/goals');
                $goalData = array_merge($goalData, ['image' => $imageUrl]);
            }

            $newGoal = $this->model::create($goalData);

            return new SuccessfulData('Create goal successfully!', ['goal' => $newGoal]);
        } catch (Exception $error) {
            return new FailedData('Failed to create goal!', ['error' => $error]);
        }
    }

    public function get(User $user, array $inputs): object
    {
        try {
            $search = isset($inputs['search']) ? $inputs['search'] : null;
            $type = isset($inputs['type']) ? $inputs['type'] : null;
            $status = isset($inputs['status']) ? $inputs['status'] : 2;

            $statuses = config('goal.goalstatus');
            $types = config('goal.goaltypes');

            $goals = $user->goals();

            if ($search) {
                $goals->where('name', 'LIKE', '%' . $search . '%');
            }

            if ($type) {
                if (!in_array($type, $types)) {
                    $message = 'Invalid goal type';
                    return new FailedData($message, ['type' => $message]);
                }
                $goals->where('type', $type);
            }

            if (!in_array($status, $statuses)) {
                $message = 'Invalid goal status';
                return new FailedData($message, ['status' => $message]);
            }

            $goals = $goals->get()->map(function ($goal) {
                $totalContributions = $this->getTotalContributions($goal->id);
                $goal->total_contributions = $totalContributions;

                return $goal;
            });

            if ($status === 0) {
                $goals = $goals->filter(function ($goal) {
                    return Carbon::today()->lt(Carbon::parse($goal->date_begin));
                });
            }

            if ($status === 1) {
                $goals = $goals->filter(function ($goal) {
                    return Carbon::today()->gte(Carbon::parse($goal->date_begin))
                        && Carbon::today()->lte(Carbon::parse($goal->date_end))
                        && $goal->amount > $goal->total_contributions;
                });
            }

            if ($status === 2) {
                $goals = $goals->filter(function ($goal) {
                    return $goal->amount <= $goal->total_contributions;
                });
            }

            if ($status === 3) {
                $goals = $goals->filter(function ($goal) {
                    return Carbon::today()->gt(Carbon::parse($goal->date_end))
                        && $goal->amount > $goal->total_contributions;
                });
            }

            return new SuccessfulData('Get goals successfully', ['goals' => $goals->values(), 'count_all' => $user->goals()->count()]);
        } catch (Exception $error) {
            return new FailedData('Failed to get goals!', ['error' => $error]);
        }
    }

    public function getTransferable(User $user, array $inputs): object
    {
        try {
            $transferAmount = $inputs['transfer_amount'];
            $goals = $user->goals();

            $goals = $goals->get()->map(function ($goal) {
                $totalContributions = $this->getTotalContributions($goal->id);
                $goal->total_contributions = $totalContributions;

                return $goal;
            });

            $goals = $goals->filter(function ($goal) use ($transferAmount) {
                return Carbon::today()->gte(Carbon::parse($goal->date_begin))
                    && Carbon::today()->lte(Carbon::parse($goal->date_end))
                    && $goal->amount - $goal->total_contributions >= $transferAmount;
            })->values();

            return new SuccessfulData('Get goals successfully', ['goals' => $goals]);
        } catch (Exception $error) {
            return new FailedData('Failed to get goals!', ['error' => $error]);
        }
    }

    public function transferToAnotherGoal(int $goalId, array $data)
    {
        try {
            $goalFrom = $this->getById($goalId);
            $goalTo = $this->getById($data['goal_to_id']);

            $goalFromContributions = $this->getTotalContributions($goalFrom->id);

            $surplus = $goalFromContributions - $goalFrom->amount;
            if ($surplus <= 0) {
                return new FailedData('Not enough money to transfer!');
            }

            $newWithdrawal = app(GoalAdditionService::class)->createGoalAddition([
                'goal_id' => $goalFrom->id,
                'amount' => $surplus * (-1),
                'goal_from_id' => $goalTo->id,
                'date' => today(),
            ]);

            $newAddition = app(GoalAdditionService::class)->createGoalAddition([
                'goal_id' => $goalTo->id,
                'amount' => $surplus,
                'goal_from_id' => $goalFrom->id,
                'date' => today(),
            ]);

            return new SuccessfulData('Transfer to another goal successfully', [
                'new_withdrawal' => $newWithdrawal,
                'new_addition' => $newAddition,
            ]);
        } catch (Exception $error) {
            return new FailedData('Failed to transfer to another goal!', ['error' => $error]);
        }
    }

    public function returnBackToWallet(int $goalId, array $inputs)
    {
        try {
            $goalFrom = $this->getById($goalId);

            $goalFromContributions = $this->getTotalContributions($goalFrom->id);

            $surplus = $goalFromContributions - $goalFrom->amount;
            if ($surplus <= 0) {
                return new FailedData('Not enough money to transfer!');
            }

            $wallet = app(WalletServices::class)->getById($inputs['wallet_id']);
            if (!$wallet) {
                return new FailedData('Wallet not found!');
            }

            $newWithdrawal = app(GoalAdditionService::class)->createGoalAddition([
                'amount' => $surplus * -1,
                'wallet_id' => $wallet->id,
                'goal_id' => $goalId,
                'date' => today(),
            ]);

            return new SuccessfulData('Transfer to another goal successfully', [
                'lasted_addition' => $newWithdrawal,
            ]);
        } catch (Exception $error) {
            return new FailedData('Failed to transfer to another goal!', ['error' => $error]);
        }
    }

    public function getTotalContributions(int $goalId)
    {
        $goal = $this->getById($goalId);

        return $goal->goal_additions()->sum('amount');
    }

    public function update(User $user, int $id, array $data): object
    {
        try {
            $goal = $this->getById($id);
            if (!$goal) {
                return new FailedData('Goal not found!');
            }

            $statuses = config('goal.goalstatus');

            if (strtoupper($data['status']) === $statuses[1]->value() && Carbon::parse($data['date_begin'])->lt(Carbon::today())) {
                $message = 'Goal\'s begining date must be from today!';

                return new FailedData($message, ['date_begin' => $message]);
            }

            if (Carbon::parse($goal->date_end)->gt(Carbon::parse($data['date_end']))) {
                $message = 'Goal\'s ending date must be after the old one!';

                return new FailedData($message, ['date_end' => $message]);
            }

            $image = isset($data['image']) ? $data['image'] : null;
            if ($image) {

                if ($goal->image) {
                    $imagePath = Str::after($goal->image, '/storage');
                    StorageHelper::delete($imagePath);
                }

                $imageUrl = StorageHelper::store($image, '/public/images/goals');
                $data = array_merge($data, ['image' => $imageUrl]);
            }

            $goal->update($data);

            return new SuccessfulData('Update goal successfully!');
        } catch (Exception $error) {
            return new FailedData('Failed to update goal!', ['error' => $error]);
        }
    }

    public function delete(int $id): object
    {
        try {
            $goal = $this->getById($id);

            if (!$goal) {
                return new FailedData('Goal not found!');
            }

            if ($goal) {
                app(GoalAdditionService::class)->deleteByGoalId($goal->id);
            }

            if ($goal->image) {
                $imagePath = Str::after($goal->image, '/storage');
                StorageHelper::delete($imagePath);
            }

            $this->model::destroy($id);

            return new SuccessfulData('Delete category successfully!');
        } catch (Exception $error) {
            return new FailedData('Failed to delete category!', ['error' => $error]);
        }
    }

    public function checkExistsByName(array $data): bool
    {
        return $this->model::where([
            'user_id' => $data['user_id'],
            'name' => $data['name'],
        ])->exists();
    }

    public function getById(int $id): ?Goal
    {
        return $this->model::find($id);
    }
}
