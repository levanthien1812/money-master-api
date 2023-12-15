<?php

namespace App\Http\Services;

use App\Exports\ReportExport;
use App\Http\Helpers\FailedData;
use App\Http\Helpers\SuccessfulData;
use App\Models\Transaction;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ReportService
{
    public function get(User $user, array $inputs): object
    {
        try {
            $reportTypes = config('report.reporttypes');
            $transactionTypes = config('report.transactiontypes');

            $month = isset($inputs['month']) ? $inputs['month'] : null;
            $year = isset($inputs['year']) ? $inputs['year'] : null;
            $wallet = isset($inputs['wallet']) ? $inputs['wallet'] : null;
            $transactionType = isset($inputs['transaction_type']) ? $inputs['transaction_type'] : $transactionTypes['TOTAL'];
            $reportType = isset($inputs['report_type']) ? $inputs['report_type'] : $reportTypes['DAY_MONTH'];

            $categoryTypes = config('category.categorytypes');

            // FILTER TRANSACTIONS BY USERS, YEARS
            $transactions = $user->transactions()->whereYear('date', $year);

            // FILTER TRANSACTIONS BY WALLETS
            if ($wallet) {
                $transactions->where('wallet_id', $wallet);
            }

            $transactionTotals = [];
            $categoriesTotals = [];

            // REPORTS BY YEAR
            if ($year && ! $month) {
                foreach ($user->categories as $category) {
                    if ($category->transactions()->count() == 0) {
                        continue;
                    }
                    $transactionsY = clone $transactions;
                    $transacs = $transactionsY->where('category_id', $category->id)->get();

                    foreach ($transacs as $transaction) {
                        $transactionDate = \DateTime::createFromFormat('Y-m-d', $transaction->date);
                        $transactionMonth = $transactionDate->format('n');

                        // REPORTS BY REPORT TYPE: TOTAL EXPENSES/INCOMES
                        if ($reportType == $reportTypes['DAY_MONTH']) {
                            if (! (bool) isset($transactionTotals[$transactionMonth][$category->type])) {
                                $transactionTotals[$transactionMonth][$category->type] = 0;
                            }

                            $transactionTotals[$transactionMonth][$category->type] += $transaction->amount;
                        }
                        // // REPORTS BY REPORT TYPE: TOTAL AMOUNT PER CATEGORY
                        elseif ($reportType == $reportTypes['CATEGORY']) {
                            $categoriesTotals[$category->id] = $category;
                            if (! isset($categoriesTotals[$category->id][$category->type])) {
                                $categoriesTotals[$category->id][$category->type] = 0;
                            }

                            $categoriesTotals[$category->id][$category->type] += $transaction->amount;
                        }
                    }
                }
            }

            // REPORTS BY MONTH
            if ($month && $year) {
                foreach ($user->categories as $category) {
                    $transactionsYM = clone $transactions;
                    // FILTER TRANSACTIONS BY MONTH
                    $transacs = $transactionsYM->where('category_id', $category->id)
                        ->whereMonth('date', $month)
                        ->get();

                    foreach ($transacs as $transaction) {
                        $transactionDate = \DateTime::createFromFormat('Y-m-d', $transaction->date);
                        $day = $transactionDate->format('j');

                        if ($reportType == $reportTypes['DAY_MONTH']) {
                            if (! (bool) isset($transactionTotals[$day][$category->type])) {
                                $transactionTotals[$day][$category->type] = 0;
                            }

                            $transactionTotals[$day][$category->type] += $transaction->amount;
                        } elseif ($reportType == $reportTypes['CATEGORY']) {
                            $categoriesTotals[$category->id] = $category;
                            if (! isset($categoriesTotals[$category->id][$category->type])) {
                                $categoriesTotals[$category->id][$category->type] = 0;
                            }

                            $categoriesTotals[$category->id][$category->type] += $transaction->amount;
                        }
                    }
                }
            }

            // FILTER BY TRANSACTION TYPE: TOTAL/INCOMES/EXPENSES
            if ($transactionType != $transactionTypes['TOTAL']) {
                $filteredTotals = [];

                if ($reportType == $reportTypes['DAY_MONTH']) {
                    foreach ($transactionTotals as $item => $total) {
                        if (isset($total[$transactionType])) {
                            $filteredTotals[$item][$transactionType] = $total[$transactionType];
                        }
                    }

                    $transactionTotals = $filteredTotals;
                } elseif ($reportType == $reportTypes['CATEGORY']) {

                    foreach ($categoriesTotals as $item => $total) {
                        if (isset($total[$transactionType])) {
                            $filteredTotals[$item] = $total;
                            $filteredTotals[$item]['amount'] = $total[$transactionType];
                        }
                    }

                    $categoriesTotals = $filteredTotals;
                }
            } else {
                if ($reportType == $reportTypes['CATEGORY']) {
                    foreach ($categoriesTotals as $item => $total) {
                        if (isset($total[$categoryTypes['EXPENSES']])) {
                            $categoriesTotals[$item]['amount'] = $total[$categoryTypes['EXPENSES']];
                        } else {
                            $categoriesTotals[$item]['amount'] = $total[$categoryTypes['INCOMES']];
                        }
                    }
                } else {
                    foreach ($transactionTotals as $item => $total) {
                        foreach ($categoryTypes as $type) {
                            if (! isset($transactionTotals[$item][$type])) {
                                $transactionTotals[$item][$type] = 0;
                            }
                        }
                    }
                }
            }

            if ($reportType == $reportTypes['DAY_MONTH']) {
                return new SuccessfulData('Get transactions successfully!', ['reports' => $transactionTotals]);
            } elseif ($reportType == $reportTypes['CATEGORY']) {
                return new SuccessfulData('Get transactions successfully!', ['reports' => $categoriesTotals]);
            }
        } catch (Exception $error) {
            return new FailedData('Failed to get reports!');
        }
    }

    public function getUserQuantityPerMonth(array $inputs): object
    {
        try {
            $year = isset($inputs['year']) ? $inputs['year'] : null;

            $userRegistrations = User::select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(*) as count')
            )
                ->whereYear('created_at', $year)
                ->groupBy(DB::raw('MONTH(created_at)'))
                ->orderBy(DB::raw('MONTH(created_at)'))
                ->get();

            $result = [];
            foreach ($userRegistrations as $registration) {
                $result[date('F', mktime(0, 0, 0, $registration->month, 1))] = $registration->count;
            }

            return new SuccessfulData('Get user quantity per month successfully!', ['quantities' => $result]);
        } catch (Exception $error) {
            return new FailedData('Failed to get user quantity per month!');
        }
    }

    public function getTransactionQuantityPerMonth(array $inputs): object
    {
        try {
            $year = isset($inputs['year']) ? $inputs['year'] : null;

            $transactionsCreated = Transaction::select(
                DB::raw('MONTH(date) as month'),
                DB::raw('COUNT(*) as count')
            )
                ->whereYear('date', $year)
                ->groupBy(DB::raw('MONTH(date)'))
                ->orderBy(DB::raw('MONTH(date)'))
                ->get();

            $result = [];
            foreach ($transactionsCreated as $transaction) {
                $result[date('F', mktime(0, 0, 0, $transaction->month, 1))] = $transaction->count;
            }

            return new SuccessfulData('Get transaction quantity per month successfully!', ['quantities' => $result]);
        } catch (Exception $error) {
            return new FailedData('Failed to get transaction quantity per month!');
        }
    }

    public function export(User $user, array $inputs)
    {
        $month = isset($inputs['month']) ? $inputs['month'] : null;
        $year = isset($inputs['year']) ? $inputs['year'] : null;

        $response = Excel::download(new ReportExport($month, $year, $user->id), 'transactions.xlsx', \Maatwebsite\Excel\Excel::XLSX);

        return $response;
    }
}
