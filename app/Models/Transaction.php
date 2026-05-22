<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'household_id',
        'account_id',
        'category_id',
        'user_id',
        'type',
        'amount',
        'description',
        'date',
        'transfer_to_account_id',
    ];

    protected $casts = [
        'amount' => 'integer',
        'date' => 'date',
    ];

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transferToAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'transfer_to_account_id');
    }
}
