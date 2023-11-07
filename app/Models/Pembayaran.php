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
            $model->url = bin2hex(random_bytes(3));
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function role_pembayarans()
    {
        return $this->hasMany(RolePembayaran::class);
    }

    public function pembayaran_users()
    {
        return $this->hasMany(PembayaranUser::class);
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
