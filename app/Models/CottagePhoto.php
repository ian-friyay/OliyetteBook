<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CottagePhoto extends Model
{
    public $timestamps = false;

    protected $fillable = ['cottage_id', 'path', 'sort_order'];

    protected function casts(): array
    {
        return [
            'cottage_id' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function cottage()
    {
        return $this->belongsTo(Cottage::class);
    }
}
