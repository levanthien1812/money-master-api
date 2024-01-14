<?php

namespace App\Http\Services;

use App\Http\Helpers\FailedData;
use App\Http\Helpers\SuccessfulData;
use App\Models\CategoryPlan;
use App\Models\User;
use Exception;

class CategoryPlanService extends BaseService
{
    protected $categoryService;

    protected $walletService;

    public function __construct(CategoryServices $categoryService, WalletServices $walletService)
    {
        parent::__construct(CategoryPlan::class);
        $this->categoryService = $categoryService;
        $this->walletService = $walletService;
    }

    public function create(User $user, array $data): object
    {
        try {

            if (!$this->walletService->checkExistsById($data['wallet_id'])) {
                return new FailedData('Wallet not found!');
            }

            if (!$this->categoryService->checkExistsById($data['category_id'])) {
                return new FailedData('Category not found!');
            }

            if (isset($data['category_id'])) {
                $category = $this->categoryService->getById($data['category_id']);

                if ($category->default == 1) {
                    $userCategory = $this->categoryService->getWithSameNameOfUser($user->id, $category->id, $category->name);

                    if (!$userCategory) {
                        $category = $this->categoryService->createBasedOnDefault($user->id, $category);
                        $data['category_id'] = $category->id;
                    } else {
                        $data['category_id'] = $userCategory->id;
                    }
                }
            }

            $planData = array_merge($data, ['user_id' => $user->id]);

            if ($this->checkExists($planData)) {
                return new FailedData('Plan is already exists!');
            }

            $newPlan = $this->model::create($planData);

            if ($newPlan) {
                app(NotificationService::class)->notifyOverspendCategoryPlan($user, $newPlan);
            }

            return new SuccessfulData('Create category plan successfully!', ['plan' => $newPlan]);
        } catch (Exception $error) {
            return new FailedData('Failed to create category plan!');
        }
    }

    public function get(User $user, array $inputs): object
    {
        try {
            $month = isset($inputs['month']) ? $inputs['month'] : null;
            $year = isset($inputs['year']) ? $inputs['year'] : null;
            $walletId = isset($inputs['wallet_id']) ? $inputs['wallet_id'] : null;
            $categoryId = isset($inputs['category_id']) ? $inputs['category_id'] : null;
            $withReport = isset($inputs['with_report']) ? $inputs['with_report'] : null;

            $reportTypes = config('report.reporttypes');

            $plans = $user->category_plans()->where('month', $month)->where('year', $year)->where('wallet_id', $walletId)->with('category');

            if ($categoryId && $plans->count() > 0) {
                $category = $this->categoryService->getById($categoryId);

                if ($category->default == 1) {
                    $category = $this->categoryService->getWithSameNameOfUser($user->id, $categoryId, $category->name);
                }

                if ($category) {
                    $plans = $plans->where('category_id', $category->id);
                } else {
                    $plans = [];
                }
            }

            if ($plans != []) {
                $plans = $plans->get();

                if ($withReport) {
                    $report = app(ReportService::class)->get($user, [
                        'year' => $year, 'month' => $month, 'wallet' => $walletId, 'report_type' => $reportTypes['CATEGORY'],
                    ]);

                    $plans = $plans->map(function ($plan) use ($report) {

                        if (isset($report->getData()['reports'][$plan->category_id . ''])) {
                            $plan = (object) array_merge($plan->toArray(), ['actual' => $report->getData()['reports'][$plan->category_id . '']['amount']]);
                        } else {
                            $plan = (object) array_merge($plan->toArray(), ['actual' => 0]);
                        }

                        return $plan;
                    });
                }
            }

            return new SuccessfulData('Get plans successfully', ['plans' => $plans]);
        } catch (Exception $error) {
            return new FailedData('Failed to get category plans!');
        }
    }

    public function update(User $user, array $data, int $id): object
    {
        try {
            $plan = $this->getById($id);

            if (!$plan) {
                return new FailedData('Category plan not found!');
            }

            $planData = array_merge($data, ['user_id' => $user->id]);

            $updated = $plan->update($planData);

            app(NotificationService::class)->notifyOverspendCategoryPlan($user, $plan);

            return new SuccessfulData('Update plan successfully!');
        } catch (Exception $error) {
            return new FailedData('Failed to update category plan!');
        }
    }

    public function delete(int $id): object
    {
        try {
            $deleted = $this->model::destroy($id);
            if (!$deleted) {
                return new FailedData('Delete fails or plan not found!');
            }

            return new SuccessfulData('Delete plan successfully!');
        } catch (Exception $error) {
            return new FailedData('Failed to delete category plan!');
        }
    }

    public function getByCategoryId(int $userId, int $categoryId, int $month, int $year): ?CategoryPlan
    {
        return $this->model::where('user_id', $userId)
            ->where('category_id', $categoryId)
            ->where('month', $month)
            ->where('year', $year)
            ->first();
    }

    public function deleteByCategoryid(int $categoryId): bool
    {
        return $this->model::where('category_id', $categoryId)->delete();
    }

    public function deleteByWalletId(int $walletId): bool
    {
        return $this->model::where('wallet_id', $walletId)->delete();
    }

    public function checkExists(array $data): bool
    {
        return CategoryPlan::where([
            'user_id' => $data['user_id'],
            'month' => $data['month'],
            'year' => $data['year'],
            'category_id' => $data['category_id'],
            'wallet_id' => $data['wallet_id'],
        ])->exists();
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
            return new FailedData('Failed to get years of category plans!');
        }
    }
}
