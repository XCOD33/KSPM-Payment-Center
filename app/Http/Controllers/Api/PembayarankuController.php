<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Models\User;
use Illuminate\Http\Request;

class PembayarankuController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth:sanctum');
    $this->user = auth('sanctum')->user();
  }

  private function get_pembayaran()
  {
    $user = User::where('id', $this->user->id)->first();
    foreach ($user->roles as $role) {
      $role_id = $role->id;
      $pembayaran = Pembayaran::whereHas('role_pembayarans', function ($query) use ($role_id) {
        $query->where('role_id', $role_id);
      })->get();
      $pembayarans = $pembayaran;
    }
    return $pembayarans;
  }

  public function simple()
  {
    $myBills = 0;
    $totalMyBills = 0;
    $paidBills = 0;
    $totalPaidBills = 0;
    $pembayarans = $this->get_pembayaran();
    foreach ($pembayarans as $pembayaran) {
      if ($pembayaran->pembayaran_users->isEmpty()) {
        $myBills++;
        $totalMyBills += $pembayaran->nominal;
      } else {
        foreach ($pembayaran->pembayaran_users as $pembayaran_user) {
          if ($pembayaran_user->user_id == $this->user->id) {
            if ($pembayaran_user->status == 'PAID') {
              $paidBills++;
              $totalPaidBills += $pembayaran_user->total;
            } else {
              $myBills++;
              $totalMyBills += $pembayaran->nominal;
            }
          }
        }
      }
    }

    return response()->json([
      'success' => true,
      'message' => 'Data pembayaran berhasil diambil',
      'data' => [
        'myBills' => $myBills,
        'totalMyBills' => "Rp " . number_format($totalMyBills, 0, ',', '.'),
        'paidBills' => $paidBills,
        'totalPaidBills' => "Rp " . number_format($totalPaidBills, 0, ',', '.')
      ]
    ]);
  }

  public function extended()
  {
  }
}
