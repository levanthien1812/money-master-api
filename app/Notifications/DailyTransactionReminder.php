<?php

namespace App\Notifications;

use App\Models\Category;
use App\Models\CategoryPlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Date;

class DailyTransactionReminder extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        private User $user
    ) {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->line('Don\'t forget to add your transactions for today!')
            ->action('Add Transactions', '/transactions')
            ->line('Thank you for using Money Master!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'date' => Carbon::now(),
            'link' => '/transactions',
            'message' => 'Don\'t forget to add your transactions for today!'
        ];
    }
}
