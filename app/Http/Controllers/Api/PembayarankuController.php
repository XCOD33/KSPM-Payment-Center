<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Models\PembayaranUser;
use App\Models\User;
use Illuminate\Http\Request;
use Nekoding\Tripay\Networks\HttpClient;
use Nekoding\Tripay\Tripay;

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
      $pembayarans = Pembayaran::whereHas('role_pembayarans', function ($query) use ($role_id) {
        $query->where('role_id', $role_id);
      })->get();
    }
    return $pembayarans;
  }

  private function get_pembayarans_with_pembayaran_user()
  {
    $user = User::where('id', $this->user->id)->first();
    foreach ($user->roles as $role) {
      $role_id = $role->id;
      $pembayarans = Pembayaran::whereHas('role_pembayarans', function ($query) use ($role_id) {
        $query->where('role_id', $role_id);
      })->with(['pembayaran_users' => function ($query) use ($user) {
        $query->where('user_id', $user->id);
      }])->get();
    }
    return $pembayarans;
  }

  private function get_pembayaran_user($pembayaran_id = null, $except_pembayaran = false)
  {
    $user = User::where('id', $this->user->id)->first();
    foreach ($user->roles as $role) {
      $role_id = $role->id;
      if ($pembayaran_id) {
        if ($except_pembayaran) {
          $pembayaran_users = PembayaranUser::whereHas('pembayaran.role_pembayarans', function ($query) use ($role_id) {
            $query->where('role_id', $role_id);
          })->where('user_id', $user->id)->where('pembayaran_id', $pembayaran_id)->first();
        } else {
          $pembayaran_users = PembayaranUser::with('pembayaran')->whereHas('pembayaran.role_pembayarans', function ($query) use ($role_id) {
            $query->where('role_id', $role_id);
          })->where('user_id', $user->id)->where('pembayaran_id', $pembayaran_id)->first();
        }
      } else {
        $pembayaran_users = PembayaranUser::with('pembayaran')->whereHas('pembayaran.role_pembayarans', function ($query) use ($role_id) {
          $query->where('role_id', $role_id);
        })->where('user_id', $user->id)->get();
      }
    }
    return $pembayaran_users;
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

  public function bills()
  {
    foreach ($this->get_pembayarans_with_pembayaran_user() as $pembayaran) {
      $pembayaran_user = $pembayaran->pembayaran_users;
      if ($pembayaran_user->isEmpty()) {
        $pembayaran->pembayaran_users()->create([
          'pembayaran_id' => $pembayaran->id,
          'user_id' => $this->user->id,
          'payment_method' => null,
          'total_fee' => 0,
          'subtotal' => 0,
          'total' => 0,
          'status' => 'UNPAID'
        ]);
      }
    }

    return response()->json([
      'success' => true,
      'message' => 'Data pembayaran berhasil diambil',
      'data' => $this->get_pembayaran_user()
    ]);
  }

  public function bill_detail($url)
  {
    $tripay = new Tripay(new HttpClient(config('app.tripay_api_key')));

    $pembayaran = Pembayaran::where('url', $url)->first();
    if (!$pembayaran) {
      return response()->json([
        'success' => false,
        'message' => 'Data pembayaran tidak ditemukan'
      ], 404);
    }

    $fees = $tripay->getChannelPembayaran()['data'];
    $channels = array_map(function ($item) use ($pembayaran) {
      $item['total'] = ($item['total_fee']['flat'] + $pembayaran->nominal) + ($item['total_fee']['percent'] / 100 * ($pembayaran->nominal + $item['total_fee']['flat']));
      return $item;
    }, $fees);

    return response()->json([
      'success' => true,
      'message' => 'Data pembayaran berhasil diambil',
      'data' => [
        'pembayaran' => $pembayaran,
        'pembayaran_user' => $this->get_pembayaran_user($pembayaran->id, true),
        'channels' => $channels
      ]
    ]);
  }
}
