<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;

class RolePembayaran extends Model
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

    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}
