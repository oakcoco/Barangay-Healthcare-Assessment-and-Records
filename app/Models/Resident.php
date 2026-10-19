<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Resident extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'barangay_id', 'resident_no', 'full_name', 'birthdate',
        'sex', 'purok', 'phone', 'email', 'photo_path',
    ];

    protected $casts = ['birthdate' => 'date'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function barangay(): BelongsTo
    {
        return $this->belongsTo(Barangay::class);
    }
}