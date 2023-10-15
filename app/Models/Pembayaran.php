<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

class Pembayaran extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [
        'id',
        'uuid'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = \Ramsey\Uuid\Uuid::uuid4()->toString();
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function position_pembayarans()
    {
        return $this->hasMany(PositionPembayaran::class);
    }

    public function getBuktiPembayaranPathAttribute($value)
    {
        return asset('storage/pembayaran' . $value);
    }

    // public function getNominalAttribute($value)
    // {
    //     return 'Rp ' . number_format($value, 0, ',', '.');
    // }
}
