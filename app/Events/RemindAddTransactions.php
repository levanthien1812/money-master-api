<?php

namespace App\Events;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RemindAddTransactions implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    
    public function __construct(private User $user)
    {

    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('channel-user-'.$this->user->id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'remind-add-transactions-event';
    }

    public function broadcastWith(): array
    {
        return [
            'date' => Carbon::now(),
            'link' => '/transactions',
            'message' => 'Don\'t forget to add your transactions for today!'
        ];
    }
}
