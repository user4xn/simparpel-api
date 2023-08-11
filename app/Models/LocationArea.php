<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LocationArea extends Model
{
    use HasFactory;

    public function coordinates(): HasMany
    {
        return $this->hasMany('App\Models\LocationCoordinate', 'location_id', 'id')->select('location_id', 'long', 'lat');
    }
}
