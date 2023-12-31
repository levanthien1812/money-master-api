<?php

namespace App\Notifications;

use App\Models\CategoryPlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OverspendCategoryPlan extends Notification
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

    private function getRemaining(): float
    {
        return $this->categoryPlan->amount - $this->currentAmount;
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
        $remaining = $this->getRemaining();
        
        return (new MailMessage)
            ->line('[OVERSPEND REMINDER] ' . $monthName . " " . $this->categoryPlan->year . ": Only " . $remaining . " remaining for " . $this->categoryPlan->category->name . "!")
            ->action('Overspend warning', url('/plans'))
            ->line('Thank you for using our application!');
    }

    public function toArray(object $notifiable): array
    {
        $monthName = Carbon::create(null, $this->categoryPlan->month, 1)->format('F');
        $remaining = $this->getRemaining();

        return [
            'date' => Carbon::now(),
            'link' => '/plans',
            'message' =>  '[OVERSPEND REMINDER] ' . $monthName . " " . $this->categoryPlan->year . ": Only " . $remaining . " remaining for " . $this->categoryPlan->category->name . "!"
        ];
    }
}
