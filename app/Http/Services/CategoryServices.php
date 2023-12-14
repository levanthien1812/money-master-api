<?php

namespace App\Http\Services;

use App\Http\Helpers\FailedData;
use App\Http\Helpers\StorageHelper;
use App\Http\Helpers\SuccessfulData;
use App\Models\Category;
use App\Models\CategoryPlan;
use App\Models\Transaction;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategoryServices extends BaseService
{
    public function __construct()
    {
        parent::__construct(Category::class);
    }

    public function create(User $user, array $data): object
    {
        try {
            $types = config('category.categorytypes');

            if (!in_array($data['type'], $types)) {
                return new FailedData('Invalid type', ['type' => 'Type is invalid! Available types: ' . implode(' / ', $types)]);
            }

            if ($this->checkExists($user->id, $data['name'])) {
                return new FailedData('Category name has been used', ['name' => 'This category name has been used!']);
            }

            $image = $data['image'];
            $imageUrl = StorageHelper::store($image, '/public/images/categories');

            if ($user->hasRole('user')) {
                $categoryData = array_merge($data, ['user_id' => $user->id, 'image' => $imageUrl, 'default' => false]);
            }
            if ($user->hasRole('admin')) {
                $categoryData = array_merge($data, ['user_id' => null, 'image' => $imageUrl, 'default' => true]);
            }

            $newCategory = $this->createCategory($categoryData);

            return new SuccessfulData('Create category successfully!', ['category' => $newCategory]);
        } catch (Exception $error) {
            return new FailedData('Fail to create category!', ['error' => $error]);
        }
    }

    public function get(User $user, array $inputs): object
    {
        try {
            $type = isset($inputs['type']) ? $inputs['type'] : null;
            $default = isset($inputs['default']) ? $inputs['default'] : null;
            $ingoreExists = isset($inputs['ignore_exists']) ? $inputs['ignore_exists'] : null;
            $month = isset($inputs['month']) ? $inputs['month'] : null;
            $year = isset($inputs['year']) ? $inputs['year'] : null;
            $withPlan = isset($inputs['with_plan']) ? $inputs['with_plan'] : null;
            $walletId = isset($inputs['wallet_id']) ? $inputs['wallet_id'] : null;

            $categories = $this->model::query()->withCount('transactions');

            $categoriesTemp = clone $categories;
            $countNames = $categoriesTemp->where('user_id', $user->id)
                ->orWhereNull('user_id')->select('name', DB::raw('count(*) as count'))->groupBy('name')->distinct()->get()->toArray();

            $categories = $categories->where('user_id', $user->id)->orWhereNull('user_id')->get()->filter(function ($category) use ($countNames, $default) {
                if (!is_null($default)) {
                    if ($default == true) {
                        $includeDefault = !is_null($category->user_id);
                    } else {
                        $includeDefault = is_null($category->user_id);
                    }
                    foreach ($countNames as $name) {
                        if ($name['name'] == $category->name && $name['count'] > 1 && $includeDefault) {
                            return false;
                        }
                    }
                } else {
                    foreach ($countNames as $name) {
                        if ($name['name'] == $category->name && is_null($category->user_id) && $name['count'] > 1) {
                            return false;
                        }
                    }
                }

                return true;
            })->values();

            if ($default == 'false') {
                $categories = $categories->filter(function ($category) {
                    return $category->user_id > 0;
                })->values();
            } elseif ($default == 'true') {
                $categories = $categories->filter(function ($category) {
                    return is_null($category->user_id);
                })->values();
            }

            if ($type) {
                $categories = $categories->filter(function ($category) use ($type) {
                    return $category->type == $type;
                })->values();
            }

            // Get categories that are not used for any category plans
            if ($ingoreExists && $month && $year) {
                $existingNames = CategoryPlan::join('categories', 'categories.id', '=', 'category_plans.category_id')
                    ->select('categories.name')
                    ->distinct()
                    ->where('month', $month)
                    ->where('year', $year)
                    ->where('categories.user_id', $user->id)->get()->pluck('name');

                $categories = $categories->filter(function ($category) use ($existingNames) {
                    return !in_array($category->name, $existingNames->toArray());
                })->values();
            }

            // Get plan of categories (if exists)
            if ($withPlan && $month && $year && $walletId) {
                $categories = $categories->map(function ($category) use ($user, $month, $year, $walletId) {
                    $plan = app(CategoryPlanService::class)->get($user, ['month' => $month, 'year' => $year, 'category_id' => $category->id, 'wallet_id' => $walletId]);

                    if ($plan instanceof SuccessfulData && count($plan->getData()['plans']) > 0) {
                        $category = (object) array_merge($category->toArray(), ['plan' => $plan->getData()['plans']->first()]);
                    } else {
                        $category = (object) array_merge($category->toArray(), ['plan' => null]);
                    }

                    return $category;
                });
            }

            // Sort categories by transaction count
            $categories = $categories->sortByDesc('transactions_count')->values();

            return new SuccessfulData('Get categories successfully', ['categories' => $categories]);
        } catch (Exception $error) {
            return new FailedData('Failed to get categories', ['error' => $error]);
        }
    }

    public function getDefault(array $inputs): object
    {
        try {
            $type = isset($inputs['type']) ? $inputs['type'] : null;
            $search = isset($inputs['search']) ? $inputs['search'] : null;

            $categories = Category::where('default', 1);
            if ($type) {
                $categories->where('type', $type);
            }

            if ($search) {
                $categories->where('name', 'LIKE', '%' . $search . '%');
            }

            $categories = $categories->get()->map(function ($category) {

                $transactionsCount = Transaction::whereHas('category', function ($query) use ($category) {
                    $query->where('name', $category->name);
                })->count();

                $usersCount = User::whereHas('transactions', function ($query) use ($category) {
                    $query->whereHas('category', function ($query) use ($category) {
                        $query->where('name', $category->name);
                    });
                })
                    ->count();

                $category->transactions_count = $transactionsCount;
                $category->users_count = $usersCount;

                return $category;
            });

            return new SuccessfulData('Get default categories successfully!', ['categories' => $categories]);
        } catch (Exception $error) {
            return new FailedData('Can not get default categories!', ['error' => $error]);
        }
    }

    public function getDefaultCount(): object
    {
        try {
            $count = Category::where('default', 1)->get()->count();

            return new SuccessfulData('', ['count' => $count]);
        } catch (Exception $error) {
            return new FailedData('Failed to get count of default categories', ['error' => $error]);
        }
    }

    public function update($data, int $id): object
    {
        try {
            $category = $this->getById($id);
            if (!$category) {
                return new FailedData('Category not found!', ['error' => 'category']);
            }

            $image = isset($data['image']) ? $data['image'] : null;
            if ($image) {
                // DELETE OLD IMAGE
                if ($category->image) {
                    $imagePath = Str::after($category->image, '/storage');
                    StorageHelper::delete($imagePath);
                }

                // STORE AND RETREIVE NEW IMAGE
                $imageUrl = StorageHelper::store($image, '/public/images/categories');
            }

            $data = $image ? array_merge($data, ['image' => $imageUrl]) : $data;
            $this->updateBasedOnDefault($category->name, $data);

            $category->update($data);

            return new SuccessfulData('Update category successfully!');
        } catch (Exception $error) {
            return new FailedData($error);
        }
    }

    public function delete(int $id): object
    {
        try {
            $category = $this->getById($id);

            if (!$category) {
                return new FailedData('Category not found!');
            }

            // Delete all plans belong to this cate
            app(CategoryPlanService::class)->deleteByCategoryId($category->id);

            // Delete all transactions belongs to the category
            if ($category->default == 0) {
                foreach ($category->transactions as $transaction) {
                    if ($transaction->image) {
                        $imagePath = Str::after($transaction->image, '/storage');
                        StorageHelper::delete($imagePath);
                    }

                    app(TransactionServices::class)->delete($transaction->id);
                }

                if ($category->image) {
                    $imagePath = Str::after($category->image, '/storage');
                    StorageHelper::delete($imagePath);
                }

                $this->model::destroy($id);
            }

            return new SuccessfulData('Delete category successfully!');
        } catch (Exception $error) {
            return new FailedData('Failed to delete category!');
        }
    }

    public function deleteDefault(int $id): object
    {
        try {
            $category = $this->getById($id);

            if (!$category) {
                return new FailedData('Category not found!');
            }

            if ($category->image) {
                $imagePath = Str::after($category->image, '/storage');
                StorageHelper::delete($imagePath);
            }

            $deleted = $this->model::destroy($id);
            if (!$deleted) {
                return new FailedData('Delete fails or category not found!');
            }

            return new SuccessfulData('Delete category successfully!');
        } catch (Exception $error) {
            return new FailedData('Failed to delete default category!');
        }
    }

    public function checkExists(int $userId, string $name): bool
    {
        $isUserCategoryExists = Category::where('user_id', $userId)->where('name', $name)->exists();
        $isDefaultCategoryExists = Category::whereNull('user_id')->where('name', $name)->exists();

        return $isUserCategoryExists || $isDefaultCategoryExists;
    }

    public function checkExistsById(int $id): bool
    {
        return Category::where('id', $id)->exists();
    }

    public function getById(int $id): ?Category
    {
        return Category::find($id);
    }

    public function getWithSameNameOfUser(int $userId, int $id, string $name): ?Category
    {
        return Category::where(['user_id' => $userId, 'name' => $name])->where('id', '!=', $id)->first();
    }

    public function createCategory(array $data): ?Category
    {
        return $this->model::create($data);
    }

    public function createBasedOnDefault(int $userId, Category $defaultCategory): ?Category
    {
        return Category::create([
            'name' => $defaultCategory->name,
            'type' => $defaultCategory->type,
            'image' => $defaultCategory->image,
            'user_id' => $userId,
            'default' => false,
        ]);
    }

    public function updateBasedOnDefault(string $oldCategoryName, array $data): void
    {
        Category::where('name', $oldCategoryName)->where('default', 0)->update($data);
    }
}
