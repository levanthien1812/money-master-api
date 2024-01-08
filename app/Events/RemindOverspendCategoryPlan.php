<?php

namespace App\Events;

use App\Models\CategoryPlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RemindOverspendCategoryPlan implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
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
        return 'remind-overspend-category-plan-event';
    }

    public function broadcastWith(): array
    {
        $monthName = Carbon::create(null, $this->categoryPlan->month, 1)->format('F');
        $remaining = $this->categoryPlan->amount - $this->currentAmount;

        return ([
            'link' => '/plans',
            'message' =>  '[OVERSPEND REMINDER] ' . $monthName . " " . $this->categoryPlan->year . ": Only " . $remaining . " remaining for " . $this->categoryPlan->category->name . "!"
        ]);
    }
}
