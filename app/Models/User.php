<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name', 'phone', 'email', 'password', 'role', 'status', 'approved_by', 'approved_at',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'approved_at' => 'datetime',
        ];
    }

    public function resident(): HasOne
    {
        return $this->hasOne(Resident::class);
    }

    public function bhwProfile(): HasOne
    {
        return $this->hasOne(BhwProfile::class);
    }

    public function isResident(): bool
    {
        return $this->role === 'resident';
    }

    public function isBhw(): bool
    {
        return $this->role === 'bhw';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }
}