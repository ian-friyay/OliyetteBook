<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'booking_id',
        'user_id',
        'cottage_id',
        'rating',
        'comment',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'booking_id' => 'integer',
            'user_id' => 'integer',
            'cottage_id' => 'integer',
            'rating' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cottage()
    {
        return $this->belongsTo(Cottage::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
