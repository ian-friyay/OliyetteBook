<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'code',
        'name',
        'location',
        'description',
        'capacity',
        'amenities',
        'rate_per_day',
        'photo',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'rate_per_day' => 'decimal:2',
            'capacity' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function getPhotoUrlAttribute(): string
    {
        return $this->photo ?: '/assets/img/fallback.svg';
    }
}
