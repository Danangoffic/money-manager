<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'google_token',
        'profile_picture',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'google_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function households(): BelongsToMany
    {
        return $this->belongsToMany(Household::class, 'household_members')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function currentHouseholdMember(): HasOne
    {
        return $this->hasOne(HouseholdMember::class);
    }

    public function getHouseholdAttribute(): ?Household
    {
        return $this->households->first();
    }

    public function getHouseholdIdAttribute(): ?int
    {
        return $this->currentHouseholdMember?->household_id;
    }

    public function isHouseholdAdmin(): bool
    {
        return $this->currentHouseholdMember?->role === 'admin';
    }
}
