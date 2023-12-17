<?php

namespace App\Http\Services;

use App\Http\Helpers\FailedData;
use App\Http\Helpers\SuccessfulData;
use App\Models\User;
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
}
