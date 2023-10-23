<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PositionPembayaran extends Model
{
    use HasFactory;

    protected $guarded = [
        'id',
        'uuid'
    ];

    public function pembayaran()
    {
        return $this->belongsTo(Pembayaran::class);
    }

    public function position()
    {
        return $this->belongsTo(Position::class);
    }
}
