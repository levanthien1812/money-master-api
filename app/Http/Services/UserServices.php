<?php

namespace App\Http\Services;

use App\Http\Helpers\FailedData;
use App\Http\Helpers\StorageHelper;
use App\Http\Helpers\SuccessfulData;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserServices extends BaseService
{
    public function __construct()
    {
        parent::__construct(User::class);
    }

    public function create(array $data): ?User
    {
        $newUser = $this->model::create($data);

        return $newUser;
    }

    public function getUserByEmail(string $email): ?User
    {
        $user = $this->model::where('email', $email)->first();

        return $user;
    }

    public function getUser(int $id): ?User
    {
        $user = $this->model::find($id);

        return $user;
    }

    public function getUsers(): object
    {
        try {
            $users = $this->model::withCount('transactions')->get();

            $users = $users->filter(function ($user) {
                return $user->hasRole('user');
            })->values();

            return new SuccessfulData('Get users successfully!', ['users' => $users]);
        } catch (Exception $error) {
            return new FailedData('Fail to get users!');
        }
    }

    public function countUsers()
    {
        try {
            $count = $this->model::get()->filter(function ($user) {
                return $user->hasRole('user');
            })->values()->count();

            $currentMonthCount = $this->model::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)->get()
                ->filter(function ($user) {
                    return $user->hasRole('user');
                })->values()->count();

            return new SuccessfulData('Get users successfully!', ['count' => $count, 'currentMonthCount' => $currentMonthCount]);
        } catch (Exception $error) {
            return new FailedData('Failed to get users quantity!');
        }
    }

    public function updateUser(User $user, array $data): object
    {
        try {
            $photo = isset($data['photo']) ? $data['photo'] : null;
            if ($photo) {
                // DELETE OLD IMAGE
                if ($user->photo) {
                    $photoPath = Str::after($user->photo, '/storage');
                    StorageHelper::delete($photoPath);
                }

                // STORE AND RETREIVE NEW IMAGE
                $photoUrl = StorageHelper::store($photo, '/public/images/users');
            }

            $data = $photo ? array_merge($data, ['photo' => $photoUrl]) : $data;

            $user->update($data);

            return new SuccessfulData('Update user successfully', ['user' => $this->getById($user->id)]);
        } catch (\Throwable $th) {
            return new FailedData('Failed to update user!');
        }
    }

    public function checkExistsByEmail(string $email): bool
    {
        return $this->model::where('email', $email)->exists();
    }

    public function updatePassword(int $userId, array $data): object
    {
        try {
            $user = $this->getById($userId);

            if (! $user) {
                return new FailedData('User not found!');
            }

            if (! Hash::check($data['password'], $user->password)) {
                return new FailedData('Password is not correct!', ['password' => 'Password is not correct!']);
            }

            $user->update(['password' => Hash::make($data['newPassword'])]);

            return new SuccessfulData('Update password successfully!');
        } catch (\Throwable $th) {
            return new FailedData('Failed to update password!');
        }
    }

    public function delete(int $id): object
    {
        try {
            $user = $this->getById($id);

            if (! $user) {
                return new FailedData('User not found!');
            }

            $this->model::find($id)->delete();

            return new SuccessfulData('Delete user successfully!');
        } catch (\Throwable $th) {
            return new FailedData('Failed to delete user account!');
        }
    }

    public function getYears(): object
    {
        try {
            $years = User::selectRaw('YEAR(created_at) as year')
                ->distinct()
                ->orderBy('year')
                ->pluck('year');

            if ($years->count() === 0) {
                $years = [2023];
            }

            return new SuccessfulData('Get years of users successfully!', ['years' => $years]);
        } catch (Exception $error) {
            return new FailedData('Failed to get users years!');
        }
    }
}
