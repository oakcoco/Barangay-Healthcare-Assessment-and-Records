<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BhwProfile extends Model
{
    protected $primaryKey = 'user_id';   // this table has no "id" column
    public $incrementing = false;

    protected $fillable = ['user_id', 'worker_no', 'barangay_id', 'purok'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function barangay(): BelongsTo
    {
        return $this->belongsTo(Barangay::class);
    }
}