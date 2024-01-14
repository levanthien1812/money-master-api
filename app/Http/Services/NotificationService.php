<?php

namespace App\Http\Services;

use App\Events\RemindOverspendCategoryPlan;
use App\Events\RemindOverspendMonthPlan;
use App\Events\RemindOverspentCategoryPlan;
use App\Events\RemindOverspentMonthPlan;
use App\Http\Helpers\FailedData;
use App\Http\Helpers\SuccessfulData;
use App\Models\CategoryPlan;
use App\Models\MonthPlan;
use App\Models\User;
use App\Notifications\OverspendCategoryPlan;
use App\Notifications\OverspendMonthPlan;
use App\Notifications\OverspentCategoryPlan;
use App\Notifications\OverspentMonthPlan;
use Exception;
use Illuminate\Notifications\Notification;

class NotificationService extends BaseService
{

    public function __construct()
    {
        parent::__construct(Notification::class);
    }

    public function get(User $user): object
    {
        try {
            $notifcations = $user->notifications;

            return new SuccessfulData('Get notifications successfully', ['notifications' => $notifcations]);
        } catch (Exception $error) {
            return new FailedData('Fail to get notifications');
        }
    }

    public function markAsRead(User $user, string $notificationId): object
    {
        try {
            $notifcation = $user->unreadNotifications()->find($notificationId);

            if ($notifcation) {
                $notifcation->markAsRead();
            }

            return new SuccessfulData('Mark notification as read successfully!');
        } catch (\Throwable $th) {
            return new FailedData('Fail to mark notification as read');
        }
    }

    public function markAllAsRead(User $user): object
    {
        try {
            $user->unreadNotifications->markAsRead();

            return new SuccessfulData('Mark all notifications as read successfully!');
        } catch (Exception $error) {
            return new FailedData('Fail to mark all notifications as read', ['error' => $error]);
        }
    }

    public function delete(User $user, string $notificationId): object
    {
        try {
            $notifcation = $user->notifications()->find($notificationId);

            if ($notifcation) {
                $notifcation->delete();
            }

            return new SuccessfulData('Notification is deleted successfully!');
        } catch (Exception $error) {
            return new FailedData('Fail to delete notification', ['error' => $error]);
        }
    }

    public function notifyOverspendCategoryPlan(User $user, CategoryPlan $categoryPlan): void
    {
        $reportTypes = config('report.reporttypes');
        $month = $categoryPlan->month;
        $year = $categoryPlan->year;
        $walletId = $categoryPlan->walletId;

        $report = app(ReportService::class)->get($user, [
            'month' => $month,
            'year' => $year,
            'wallet' => $walletId,
            'report_type' => $reportTypes['CATEGORY']
        ]);

        $currentAmount = $report->getData()['reports'][$categoryPlan->category_id . '']['amount'];

        $currentPercent = $currentAmount / $categoryPlan->amount * 100;
        if ($currentPercent >= 95 && $currentPercent <= 100) {
            $user->notify(new OverspendCategoryPlan($user, $categoryPlan, $currentAmount));
            event(new RemindOverspendCategoryPlan($user, $categoryPlan, $currentAmount));
        } else if ($currentPercent > 100) {
            $user->notify(new OverspentCategoryPlan($user, $categoryPlan, $currentAmount));
            event(new RemindOverspentCategoryPlan($user, $categoryPlan, $currentAmount));
        }
    }

    public function notifyOverspendMonthPlan(User $user, MonthPlan $monthPlan): void
    {
        $reportTypes = config('report.reporttypes');
        $categoryTypes = config('category.categorytypes');
        $month = $monthPlan->month;
        $year = $monthPlan->year;
        $walletId = $monthPlan->walletId;

        $report = app(ReportService::class)->get($user, [
            'year' => $year,
            'wallet' => $walletId,
            'report_type' => $reportTypes['DAY_MONTH']
        ]);

        $currentAmount = $report->getData()['reports'][$month][$categoryTypes['EXPENSES']];

        $currentPercent = $currentAmount / $monthPlan->amount * 100;
        if ($currentPercent >= 95 && $currentPercent <= 100) {
            $user->notify(new OverspendMonthPlan($user, $monthPlan, $currentAmount));
            event(new RemindOverspendMonthPlan($user, $monthPlan, $currentAmount));
        } else if ($currentPercent > 100) {
            $user->notify(new OverspentMonthPlan($user, $monthPlan, $currentAmount));
            event(new RemindOverspentMonthPlan($user, $monthPlan, $currentAmount));
        }
    }
}
