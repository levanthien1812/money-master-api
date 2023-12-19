<?php

namespace App\Console\Commands;

use App\Events\RemindAddTransactions;
use App\Models\User;
use App\Notifications\DailyTransactionReminder;
use Illuminate\Console\Command;

class DailyTransactionsReminderCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transactions:reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reminder user to add transactions daily';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = User::whereDoesntHave('transactions', function ($query) {
            $query->whereDate('created_at', now()->toDateString());
        })->get();

        foreach ($users as $user) {
            if ($user->hasRole('user')) {
                $user->notify(new DailyTransactionReminder($user));
                event(new RemindAddTransactions($user));
            }
        }
    }
}
