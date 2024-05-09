<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

class PembayaranUser extends Model
{
  use HasFactory, SoftDeletes;

  protected $guarded = [
    'id'
  ];

  public function pembayaran()
  {
    return $this->belongsTo(Pembayaran::class);
  }

  public function user()
  {
    return $this->belongsTo(User::class, 'user_id', 'id');
  }

  protected static function boot()
  {
    parent::boot();

    static::creating(function ($model) {
      $model->invoice_id = 'INV-' . strtoupper(bin2hex(random_bytes(3)));
      $model->uuid = Uuid::uuid4();
    });
  }
}
