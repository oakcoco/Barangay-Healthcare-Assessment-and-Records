<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Barangay extends Model
{
    protected $fillable = ['name'];

    public function residents(): HasMany
    {
        return $this->hasMany(Resident::class);
    }

    public function bhwProfiles(): HasMany
    {
        return $this->hasMany(BhwProfile::class);
    }
}