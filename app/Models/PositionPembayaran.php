<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PositionPembayaran extends Model
{
    use HasFactory, SoftDeletes;

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
