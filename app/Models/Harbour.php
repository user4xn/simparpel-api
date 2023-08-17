<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Harbour extends Model
{
    use HasFactory;

    public function getCreatedAtAttribute($value)
    {
        return date('Y-m-d H:i:s', strtotime($value));
    }
    
    public function getUpdatedAtAttribute($value)
    {
        return date('Y-m-d H:i:s', strtotime($value));
    }

    public function coordinates(): HasMany
    {
        return $this->hasMany('App\Models\HarbourGeofence', 'harbour_id', 'id')->select('harbour_id', 'long', 'lat');
    }
}
