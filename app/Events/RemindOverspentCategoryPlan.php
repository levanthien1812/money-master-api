<?php

namespace App\Events;

use App\Models\CategoryPlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RemindOverspentCategoryPlan
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        private User $user,
        private CategoryPlan $categoryPlan,
        private float $currentAmount
    ) {
        //
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('channel-user-' . $this->user->id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'remind-overspent-category-plan-event';
    }

    public function broadcastWith(): array
    {
        $monthName = Carbon::create(null, $this->categoryPlan->month, 1)->format('F');
        $exceeding = $this->currentAmount - $this->categoryPlan->amount;

        return ([
            'link' => '/plans',
            'message' =>  '[OVERSPENT REMINDER] ' . $monthName . " " . $this->categoryPlan->year . ": Exceed " . $exceeding . " for " . $this->categoryPlan->category->name . "!"
        ]);
    }
}
