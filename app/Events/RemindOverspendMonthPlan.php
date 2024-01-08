<?php

namespace App\Events;

use App\Models\MonthPlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RemindOverspendMonthPlan implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        private User $user,
        private MonthPlan $monthPlan,
        private float $currentAmount
    ) {
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
        return 'remind-overspend-month-plan-event';
    }

    public function broadcastWith(): array
    {
        $monthName = Carbon::create(null, $this->monthPlan->month, 1)->format('F');
        $exceeding = $this->currentAmount - $this->monthPlan->amount;

        return ([
            'link' => '/plans',
            'message' =>  '[OVERSPEND REMINDER] ' . $monthName . " " . $this->monthPlan->year . ": Only " . $exceeding . " remaining!"
        ]);
    }
}
