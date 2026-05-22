<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Goal extends Model
{
    use HasFactory;

    protected $fillable = ['household_id', 'name', 'target_amount', 'current_amount', 'deadline'];

    protected $casts = [
        'target_amount' => 'integer',
        'current_amount' => 'integer',
        'deadline' => 'date',
    ];

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    public function getPercentageAttribute(): float
    {
        if ($this->target_amount === 0) {
            return 0;
        }

        return min(100, round(($this->current_amount / $this->target_amount) * 100, 1));
    }
}
