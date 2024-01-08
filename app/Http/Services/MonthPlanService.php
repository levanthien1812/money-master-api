<?php

namespace App\Http\Services;

use App\Http\Helpers\FailedData;
use App\Http\Helpers\SuccessfulData;
use App\Models\MonthPlan;
use App\Models\User;
use Exception;

class MonthPlanService extends BaseService
{
    protected $walletService;

    public function __construct(WalletServices $walletService)
    {
        parent::__construct(MonthPlan::class);
        $this->walletService = $walletService;
    }

    public function create(User $user, array $data): object
    {
        try {

            if (! $this->walletService->checkExistsById($data['wallet_id'])) {
                return new FailedData('Wallet not found!');
            }

            $planData = array_merge($data, ['user_id' => $user->id]);

            if ($this->checkExists($planData)) {
                return new FailedData('Plan is already exists!');
            }

            $newPlan = $this->model::create($planData);

            return new SuccessfulData('Create month plan successfully!', ['plan' => $newPlan]);
        } catch (Exception $error) {
            return new FailedData('Failed to create month plan!');
        }
    }

    public function get(User $user, array $inputs): object
    {
        try {
            $month = isset($inputs['month']) ? $inputs['month'] : null;
            $year = isset($inputs['year']) ? $inputs['year'] : null;
            $walletId = isset($inputs['wallet_id']) ? $inputs['wallet_id'] : null;
            $withReport = isset($inputs['with_report']) ? $inputs['with_report'] : null;

            $categoryTypes = config('category.categorytypes');

            $plans = $user->month_plans()->where('year', $year)->where('wallet_id', $walletId)->orderByDesc('month');

            if ($month) {
                $plans->where('month', $month);
            }

            $plans = $plans->get();

            if ($withReport) {
                $report = app(ReportService::class)->get($user, ['year' => $year, 'wallet' => $walletId]);

                $plans = $plans->map(function ($plan) use ($report, $categoryTypes) {
                    if (isset($report->getData()['reports'][$plan['month']])) {
                        $plan = (object) array_merge($plan->toArray(), ['actual' => $report->getData()['reports'][$plan['month']][strval($categoryTypes['EXPENSES'])]]);
                    } else {
                        $plan = (object) array_merge($plan->toArray(), ['actual' => 0]);
                    }

                    return $plan;
                });
            }

            return new SuccessfulData('Get plans successfully', ['plans' => $plans]);
        } catch (Exception $error) {
            return new FailedData('Failed to get month plans!', );
        }
    }

    public function update(User $user, array $data, int $id): object
    {
        try {
            $plan = $this->getById($id);
            if (! $plan) {
                return new FailedData('Month plan not found!');
            }

            $planData = array_merge($data, ['user_id' => $user->id]);

            $updated = $plan->update($planData);

            if (! $updated) {
                return new FailedData('Plan is not created!');
            }

            return new SuccessfulData('Update plan successfully!');
        } catch (Exception $error) {
            return new FailedData('Failed to update month plan!');
        }
    }

    public function delete(int $id): object
    {
        try {
            $deleted = $this->model::destroy($id);
            if (! $deleted) {
                return new FailedData('Delete fails or plan not found!');
            }

            return new SuccessfulData('Delete plan successfully!');
        } catch (Exception $error) {
            return new FailedData('Failed to delete month plan!');
        }
    }

    public function deleteByWallet(int $walletId): bool
    {
        return $this->model::where('wallet_id', $walletId)->delete();
    }

    public function checkExists(array $data): bool
    {
        return $this->model::where([
            'user_id' => $data['user_id'],
            'month' => $data['month'],
            'year' => $data['year'],
            'wallet_id' => $data['wallet_id'],
        ])->exists();
    }

    public function getByMonthYear(int $userId, int $month, int $year): ?MonthPlan {
        return $this->model::where('user_id', $userId)
            ->where('month', $month)
            ->where('year', $year)
            ->first();
    }

    public function getYears(User $user, array $inputs): object
    {
        try {

            $walletId = isset($inputs['wallet_id']) ? $inputs['wallet_id'] : null;

            $query = $this->model::where('user_id', $user->id);

            if ($walletId) {
                $query->where('wallet_id', $walletId);
            }

            $minYear = $query->min('year');
            $maxYear = $query->max('year');

            if ($minYear == 0 && $maxYear == 0) {
                $uniqueYears = [2023];
            } else {
                $yearRange = range($minYear, $maxYear);
                $uniqueYears = array_unique($yearRange);

                sort($uniqueYears);
            }

            return new SuccessfulData('Get years of plans successfully!', ['years' => $uniqueYears]);
        } catch (Exception $e) {
            return new FailedData('Failed to get years of month plans!');
        }
    }
}
