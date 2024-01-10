<?php

namespace App\Notifications;

use App\Models\MonthPlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OverspentMonthPlan extends Notification
{
    use Queueable;

    public function __construct(
        private User $user,
        private MonthPlan $monthPlan,
        private float $currentAmount
    ) {
        //
    }

    private function getExceeding(): float
    {
        return $this->currentAmount - $this->monthPlan->amount;
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
        $exceeding = $this->getExceeding();

        return (new MailMessage)
            ->line('[OVERSPENT REMINDER] ' . $monthName . " " . $this->monthPlan->year . ": Exceeded " . ($exceeding * -1) . "!")
            ->action('Overspend warning', url('/plans'))
            ->line('Thank you for using our application!');
    }

    public function toArray(object $notifiable): array
    {
        $monthName = Carbon::create(null, $this->monthPlan->month, 1)->format('F');
        $exceeding = $this->getExceeding();

        return [
            'date' => Carbon::now(),
            'link' => '/plans',
            'message' =>  '[OVERSPENT REMINDER] ' . $monthName . " " . $this->monthPlan->year . ": Exceeded " . $exceeding . "!"
        ];
    }
}
