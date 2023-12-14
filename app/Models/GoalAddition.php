<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoalAddition extends Model
{
    use HasFactory;

    protected $fillable = [
        'amount',
        'goal_id',
        'goal_from_id',
        'wallet_id',
        'date',
        'notes',
    ];

    public function goal(): BelongsTo
    {
        return $this->belongsTo(Goal::class);
    }

    public function goal_from(): BelongsTo
    {
        return $this->belongsTo(Goal::class);
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }
}
