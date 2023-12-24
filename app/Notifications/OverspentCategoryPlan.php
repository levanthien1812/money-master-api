<?php

namespace App\Notifications;

use App\Models\CategoryPlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OverspentCategoryPlan extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        private User $user,
        private CategoryPlan $categoryPlan,
        private float $currentAmount
    ) {
        //
    }

    private function getExceeding(): float
    {
        return $this->currentAmount - $this->categoryPlan->amount;
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
        $monthName = Carbon::create(null, $this->categoryPlan->month, 1)->format('F');
        $exceeding = $this->getExceeding();

        return (new MailMessage)
            ->line('[OVERSPENT REMINDER] ' . $monthName . " " . $this->categoryPlan->year . ": Exceed " . $exceeding . " for " . $this->categoryPlan->category->name . "!")
            ->action('Overspent warning', url('/plans'))
            ->line('Thank you for using our application!');
    }

    public function toArray(object $notifiable): array
    {
        $monthName = Carbon::create(null, $this->categoryPlan->month, 1)->format('F');
        $exceeding = $this->getExceeding();

        return [
            'date' => Carbon::now(),
            'link' => '/plans',
            'message' =>  '[OVERSPENT REMINDER] ' . $monthName . " " . $this->categoryPlan->year . ": Exceed " . $exceeding . " for " . $this->categoryPlan->category->name . "!"
        ];
    }
}
