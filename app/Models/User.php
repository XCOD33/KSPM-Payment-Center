<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Ramsey\Uuid\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'member_id',
        'password',
        'year',
        'position_id',
        'nim'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'password' => 'hashed',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            try {
                $model->uuid = Uuid::uuid4()->toString();
            } catch (\Exception $e) {
                abort(500, $e->getMessage());
            }
        });
    }

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    public function pembayarans()
    {
        return $this->hasMany(Pembayaran::class);
    }

    public function position_pembayarans()
    {
        return $this->hasMany(PositionPembayaran::class);
    }

    public function pembayaran_users()
    {
        return $this->hasMany(PembayaranUser::class);
    }
}
