<?php

namespace App\Notifications;

use App\Models\MonthPlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OverspendMonthPlan extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        private User $user,
        private MonthPlan $monthPlan,
        private float $currentAmount
    ) {
        //
    }

    private function getRemaining(): float
    {
        return $this->monthPlan->amount - $this->currentAmount;
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
        $monthName = Carbon::create(null, $this->monthPlan->month, 1)->format('F');
        $remaining = $this->getRemaining();

        return (new MailMessage)
            ->line('[OVERSPEND REMINDER] ' . $monthName . " " . $this->monthPlan->year . ": Only " . $remaining . " remaining!")
            ->action('Overspend warning', url('/plans'))
            ->line('Thank you for using our application!');
    }

    public function toArray(object $notifiable): array
    {
        $monthName = Carbon::create(null, $this->monthPlan->month, 1)->format('F');
        $remaining = $this->getRemaining();

        return [
            'date' => Carbon::now(),
            'link' => '/plans',
            'message' =>  '[OVERSPEND REMINDER] ' . $monthName . " " . $this->monthPlan->year . ": Only " . $remaining . " remaining!"
        ];
    }
}
