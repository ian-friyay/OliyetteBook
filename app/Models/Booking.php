<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'receipt_no',
        'user_id',
        'cottage_id',
        'room_id',
        'check_in',
        'check_out',
        'check_in_time',
        'check_out_time',
        'adults',
        'children',
        'special_requests',
        'entrance_fee',
        'total_amount',
        'payment_method',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'cottage_id' => 'integer',
            'room_id' => 'integer',
            'adults' => 'integer',
            'children' => 'integer',
            'entrance_fee' => 'decimal:2',
            'total_amount' => 'decimal:2',
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

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function review()
    {
        return $this->hasOne(Review::class);
    }
}
