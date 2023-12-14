<?php

namespace App\Http\Services;

use App\Http\Helpers\FailedData;
use App\Http\Helpers\StorageHelper;
use App\Http\Helpers\SuccessfulData;
use App\Models\User;
use App\Models\Wallet;
use Exception;
use Illuminate\Support\Str;

class WalletServices extends BaseService
{
    public function __construct()
    {
        parent::__construct(Wallet::class);
    }

    public function create(User $user, array $data): object
    {
        try {
            if ($this->checkExistsByName($user->id, $data['name'])) {
                return new FailedData('This wallet name has been used!', ['name' => 'This wallet name has been used!']);
            }

            $image = isset($data['image']) ? $data['image'] : null;
            $imageUrl = StorageHelper::store($image, '/public/images/wallets');

            $walletData = array_merge($data, ['user_id' => $user->id, 'image' => $imageUrl]);

            $newWallet = Wallet::create($walletData);

            if ($this->countByUser($walletData['user_id']) <= 1) {
                $newWallet->default = 1;
                $newWallet->save();
            } else {
                if ($data['default'] == true) {
                    $this->updateDefaultExcept($walletData['user_id'], $newWallet->id, false);
                }
            }

            return new SuccessfulData('Create wallet successfully!', ['wallet' => $newWallet]);
        } catch (Exception $error) {
            return new FailedData($error);
        }
    }

    public function get(User $user): object
    {
        try {
            $wallets = $user->wallets()->get()->map(function ($wallet) {
                $balance = $this->getBalance($wallet->id);

                $wallet->balance = $balance;

                return $wallet;
            });

            return new SuccessfulData('Get wallets successfully', ['wallets' => $wallets]);
        } catch (Exception $error) {
            return new FailedData('Something went wrong when fetching wallets!', ['error' => $error]);
        }
    }

    public function getBalance(int $walletId)
    {
        $transactionsTotalAmount = Wallet::where('id', $walletId)->with('transactions.category')
            ->get()
            ->map(function ($wallet) {
                return $wallet->transactions->sum(function ($transaction) {
                    if ($transaction->category->type === 'incomes') {
                        return $transaction->amount;
                    } else {
                        return -$transaction->amount;
                    }
                });
            })
            ->sum();

        $goalAdditionsContributions = $this->getById($walletId)->goal_additions()->sum('amount');

        return $transactionsTotalAmount - $goalAdditionsContributions;
    }

    public function update(array $data, int $id): object
    {
        try {

            $wallet = $this->getById($id);
            if (! $wallet) {
                return new FailedData('Wallet not found!');
            }

            $image = isset($data['image']) ? $data['image'] : null;
            if ($image) {
                // DELETE OLD IMAGE
                if ($wallet->image) {
                    $imagePath = Str::after($wallet->image, '/storage');
                    StorageHelper::delete($imagePath);
                }

                // STORE AND RETREIVE NEW IMAGE
                $imageUrl = StorageHelper::store($image, '/public/images/categories');
            }

            $data = $image ? array_merge($data, ['image' => $imageUrl]) : $data;

            $wallet->update($data);

            if ($data['default'] == true) {
                $this->updateDefaultExcept($wallet->user_id, $id, false);
            }

            return new SuccessfulData('Update wallet successfully!');
        } catch (Exception $error) {
            return new FailedData('Failed to update wallet!', ['error' => $error]);
        }
    }

    public function delete(int $id): object
    {
        try {
            $wallet = $this->getById($id);

            if (! $wallet) {
                return new FailedData('Wallet not found!');
            }

            if ($wallet) {
                app(TransactionServices::class)->deleteByWallet($wallet->id);
                app(MonthPlanService::class)->deleteByWallet($wallet->id);
                app(CategoryPlanService::class)->deleteByWalletId($wallet->id);
            }

            $this->model::destroy($id);

            return new SuccessfulData('Delete category successfully!');
        } catch (Exception $error) {
            return new FailedData('Failed to delete category!', ['error' => $error]);
        }
    }

    public function checkExistsByName(int $userId, string $name): bool
    {
        return Wallet::where(['user_id' => $userId, 'name' => $name])->exists();
    }

    public function countByUser(int $userId): int
    {
        return Wallet::where('user_id', $userId)->count();
    }

    public function updateDefaultExcept(int $userId, int $walletIdExcept, bool $default): bool
    {
        return Wallet::where('user_id', $userId)->where('id', '!=', $walletIdExcept)->update(['default' => $default]);
    }

    public function checkExistsById(int $id): bool
    {
        return Wallet::where('id', $id)->exists();
    }

    public function getById(int $id): ?Wallet
    {
        return Wallet::find($id);
    }
}
