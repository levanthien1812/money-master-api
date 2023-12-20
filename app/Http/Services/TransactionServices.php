<?php

namespace App\Http\Services;

use App\Events\RemindOverspendCategoryPlan;
use App\Http\Helpers\FailedData;
use App\Http\Helpers\StorageHelper;
use App\Http\Helpers\SuccessfulData;
use App\Models\Event;
use App\Models\Transaction;
use App\Models\User;
use App\Notifications\OverspendCategoryPlan;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class TransactionServices extends BaseService
{
    protected $categoryService;
    protected $walletService;
    protected $categoryPlanService;
    protected $reportService;

    public function __construct(
        CategoryServices $categoryService,
        WalletServices $walletService,
        CategoryPlanService $categoryPlanService,
        ReportService $reportService
    ) {
        parent::__construct(Transaction::class);
        $this->categoryService = $categoryService;
        $this->walletService = $walletService;
        $this->categoryPlanService = $categoryPlanService;
        $this->reportService = $reportService;
    }

    public function create(User $user, array $data): object
    {
        try {
            $reportTypes = config('report.reporttypes');

            if (!$this->categoryService->checkExistsById($data['category_id'])) {
                return new FailedData('Category not found!');
            }

            if (!$this->walletService->checkExistsById($data['wallet_id'])) {
                return new FailedData('Wallet not found!');
            }

            $transactionData = array_merge($data, ['user_id' => $user->id]);

            $category = $this->categoryService->getById($data['category_id']);

            // CREATE CATEGORY FOR USER IF CATEGORY IS DEFAULT
            if ($category && $category->default == true) {
                $newCategory = $this->categoryService->createBasedOnDefault($user->id, $category);
            }

            if (isset($newCategory)) {
                $transactionData = array_merge($transactionData, ['category_id' => $newCategory->id]);
            }

            // STORE AND RETREIVE IMAGE
            $image = isset($data['image']) ? $data['image'] : null;
            if ($image) {
                $imageUrl = StorageHelper::store($image, '/public/images/transactions');
                $transactionData = array_merge($transactionData, ['image' => $imageUrl]);
            }

            $newTransaction = $this->model::create($transactionData);

            $month = Carbon::parse($data['date'])->format('m');
            $year = Carbon::parse($data['date'])->year;
            $categoryPlan = $this->categoryPlanService->getByCategoryId($user->id, $category->id, $month, $year);

            if ($categoryPlan) {
                $report = $this->reportService->get($user, [
                    'month' => $month,
                    'year' => $year,
                    'wallet' => $data['wallet_id'],
                    'report_type' => $reportTypes['CATEGORY']
                ]);

                $currentAmount = $report->getData()['reports'][$categoryPlan->category_id . '']['amount'];

                $currentPercent = $currentAmount / $categoryPlan->amount * 100;
                if ($currentPercent >= 95 && $currentPercent <= 100) {
                    $user->notify(new OverspendCategoryPlan($user, $categoryPlan, $currentAmount));
                    event(new RemindOverspendCategoryPlan($user, $categoryPlan, $currentAmount));
                }
            }

            return new SuccessfulData('Create transaction successfully!', ['transaction' => $newTransaction]);
        } catch (Exception $error) {
            return new FailedData('Failed to create transaction!');
        }
    }

    public function get(User $user, array $inputs): object
    {
        try {
            $day = isset($inputs['day']) ? $inputs['day'] : null;
            $month = isset($inputs['month']) ? $inputs['month'] : null;
            $year = isset($inputs['year']) ? $inputs['year'] : null;
            $wallet = isset($inputs['wallet']) ? $inputs['wallet'] : null;
            $category = isset($inputs['category']) ? $inputs['category'] : null;
            $transactionType = isset($inputs['transaction_type']) ? $inputs['transaction_type'] : 'total';
            $search = isset($inputs['search']) ? $inputs['search'] : '';

            $query = $user->transactions();

            if ($category) {
                $query->where('category_id', $category);
            }

            if ($transactionType != 'total') {
                $query->whereHas('category', function (Builder $query) use ($transactionType) {
                    $query->where('type', $transactionType);
                });
            }

            if ($wallet) {
                $query->where('wallet_id', $wallet);
            }

            if ($day) {
                $query->whereDay('date', $day);
            }

            if ($month) {
                $query->whereMonth('date', $month);
            }

            if ($year) {
                $query->whereYear('date', $year);
            }

            if (strlen($search) > 0) {
                $query->where('title', 'LIKE', '%' . ($search) . '%')->orWhere('description', 'LIKE', '%' . $search . '%');
            }

            $transactions = $query->orderBy('date', 'desc')->with('category')->get();

            return new SuccessfulData('', ['transactions' => $transactions]);
        } catch (Exception $error) {
            return new FailedData('Failed to get transactions!');
        }
    }

    public function count()
    {
        try {
            $count = $this->model::get()->count();
            $currentMonthCount = $this->model::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)->get()->count();

            return new SuccessfulData('Get users successfully!', ['count' => $count, 'currentMonthCount' => $currentMonthCount]);
        } catch (Exception $error) {
            return new FailedData('Failed to get transactions quantity');
        }
    }

    public function update(User $user, array $data, int $id): object
    {
        try {
            $transaction = $this->getById($id);
            if (!$transaction) {
                return new FailedData('Transaction not found!');
            }

            if (isset($data['category_id'])) {
                if (!$this->categoryService->checkExistsById($data['category_id'])) {
                    return new FailedData('Category not found!');
                }

                $category = $this->categoryService->getById($data['category_id']);

                if ($category && $category->default == true) {
                    $existingUserCategory = $this->categoryService->getWithSameNameOfUser($user->id, $category->id, $category->name);

                    if ($existingUserCategory) {
                        $data['category_id'] = $existingUserCategory->id;
                    } else {
                        $newCategory = $this->categoryService->createBasedOnDefault($user->id, $category);
                        $data['category_id'] = $newCategory->id;
                    }
                }
            }

            if (isset($data['wallet_id'])) {
                if (!$this->walletService->checkExistsById($data['wallet_id'])) {
                    return new FailedData('Wallet not found!');
                }
            }

            $image = isset($data['image']) ? $data['image'] : null;

            if ($image || isset($data['is_image_cleared'])) {
                // DELETE OLD IMAGE
                if ($transaction->image) {
                    $imagePath = Str::after($transaction->image, '/storage');
                    StorageHelper::delete($imagePath);
                }
            }

            if ($image) {
                $imageUrl = StorageHelper::store($image, '/public/images/transactions');
            }

            if (isset($data['is_image_cleared'])) {
                $imageUrl = '';
            }

            $transactionData = isset($imageUrl) ? array_merge($data, ['user_id' => $user->id, 'image' => $imageUrl])
                : array_merge($data, ['user_id' => $user->id]);

            $transaction->update($transactionData);

            return new SuccessfulData('Update transaction successfully!', $data);
        } catch (Exception $error) {
            return new FailedData('Failed to update transation!');
        }
    }

    public function delete(int $id)
    {
        try {
            $transaction = $this->getById($id);
            if (!$transaction) {
                return new FailedData('Transaction not found!');
            }

            if ($transaction->image) {
                $imagePath = Str::after($transaction->image, '/storage');
                StorageHelper::delete($imagePath);
            }

            $this->model::destroy($id);

            return new SuccessfulData('Delete transaction successfully!');
        } catch (Exception $error) {
            return new FailedData('Failed to delete transaction!');
        }
    }

    public function getById(int $id): ?Transaction
    {
        return Transaction::find($id);
    }

    public function deleleByCategory(int $categoryId): bool
    {
        return Transaction::where('category_id', $categoryId)->delete();
    }

    public function deleteByWallet(int $walletId): bool
    {
        return Transaction::where('wallet_id', $walletId)->delete();
    }

    public function deleteByEvent(int $eventId): bool
    {
        return Transaction::where('event_id', $eventId)->delete();
    }

    public function removeEvent(int $eventId): bool
    {
        return Transaction::where('event_id', $eventId)->update(['event_id' => null]);
    }

    public function getYears(User $user, array $inputs): object
    {
        try {
            $walletId = isset($inputs['wallet_id']) ? $inputs['wallet_id'] : null;

            $query = $this->model::where('user_id', $user->id);

            if ($walletId) {
                $query->where('wallet_id', $walletId);
            }

            $years = Transaction::selectRaw('YEAR(date) as year')
                ->distinct()
                ->orderBy('year')
                ->pluck('year');

            if ($years->count() === 0) {
                $years = [2023];
            }

            return new SuccessfulData('Get years of transactions successfully!', ['years' => $years]);
        } catch (Exception $error) {
            return new FailedData('Failed to get transactions years!');
        }
    }
}
