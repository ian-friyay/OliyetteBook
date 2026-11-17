<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cottage extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'code',
        'owner_id',
        'name',
        'location',
        'description',
        'capacity',
        'amenities',
        'rate_per_day',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'rate_per_day' => 'decimal:2',
            'capacity' => 'integer',
            'owner_id' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function photos()
    {
        return $this->hasMany(CottagePhoto::class)->orderBy('sort_order')->orderBy('id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function approvedReviews()
    {
        return $this->hasMany(Review::class)->where('status', 'approved');
    }
}
