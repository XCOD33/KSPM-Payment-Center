<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Models\PembayaranUser;
use App\Models\User;
use Illuminate\Http\Request;
use Nekoding\Tripay\Networks\HttpClient;
use Nekoding\Tripay\Signature;
use Nekoding\Tripay\Tripay;

class PembayarankuController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth:sanctum')->except('tripay_callback');
    $this->user = auth('sanctum')->user();
    $this->tripay = new Tripay(new HttpClient(config('app.tripay_api_key')));
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

    $pembayaran = Pembayaran::where('url', $url)->first();
    if (!$pembayaran) {
      return response()->json([
        'success' => false,
        'message' => 'Data pembayaran tidak ditemukan'
      ], 404);
    }

    $fees = $this->tripay->getChannelPembayaran()['data'];
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

  public function pay(Request $request, $url)
  {
    $request->validate([
      'payment_code' => 'required',
    ]);

    $pembayaran = Pembayaran::where('url', $url)->first();
    if (!$pembayaran) {
      return response()->json([
        'success' => false,
        'message' => 'Data pembayaran tidak ditemukan'
      ], 404);
    }

    $pembayaran_user = $this->get_pembayaran_user($pembayaran->id);

    $dataToTripay = [
      'method' => $request->payment_code,
      'merchant_ref' => $pembayaran_user->invoice_id,
      'amount' => $pembayaran->nominal,
      'customer_name' => $this->user->name,
      'customer_email' => $this->user->email,
      'customer_phone' => $this->user->phone,
      'order_items' => [
        [
          'name' => $pembayaran->name,
          'price' => $pembayaran->nominal,
          'quantity' => 1
        ]
      ],
      'return_url' => 'https://localhost:8000',
      'expired_time' => (time() + (24 * 60 * 60)), // 24 jam
      'signature' => Signature::generate($pembayaran_user->invoice_id . $pembayaran->nominal)
    ];

    $res = $this->tripay->createTransaction($dataToTripay, Tripay::CLOSE_TRANSACTION);

    if ($res->getResponse()['success'] === false) {
      return response()->json([
        'success' => false,
        'message' => $res->getResponse()['message']
      ]);
    }

    $data_response = $res->getResponse()['data'];
    $pembayaran_user->update([
      'payment_method' => $data_response['payment_name'],
      'payment_method_code' => $data_response['payment_method'],
    ]);

    return $res->getResponse();
  }

  public function tripay_callback(Request $request)
  {
    $callbackSignature = $request->server('HTTP_X_CALLBACK_SIGNATURE');
    $json = $request->getContent();
    $signature = hash_hmac('sha256', $json, config('app.tripay_private_key'));

    if ($signature !== (string) $callbackSignature) {
      return response()->json([
        'success' => false,
        'message' => 'Signature tidak valid'
      ]);
    }

    if ('payment_status' !== (string) $request->server('HTTP_X_CALLBACK_EVENT')) {
      return response()->json([
        'success' => false,
        'message' => 'Event tidak valid'
      ]);
    }

    $data = json_decode($json);

    if (JSON_ERROR_NONE !== json_last_error()) {
      return response()->json([
        'success' => false,
        'message' => 'Data tidak valid'
      ]);
    }

    $invoice_id = $data->merchant_ref;
    $status = strtoupper((string) $data->status);

    if ($data->is_closed_payment === 1) {
      $pembayaran_user = PembayaranUser::where('invoice_id', $invoice_id)
        ->where('status', 'UNPAID')
        ->first();

      if (!$pembayaran_user) {
        return response()->json([
          'success' => false,
          'message' => 'Data pembayaran tidak ditemukan atau sudah dibayar: ' . $invoice_id
        ]);
      }

      switch ($status) {
        case 'PAID':
          $pembayaran_user->update([
            'status' => 'PAID',
            'total_fee' => $data->total_fee,
            'subtotal' => $data->amount_received,
            'total' => $data->total_amount
          ]);
          break;

        case 'EXPIRED':
          $pembayaran_user->update([
            'status' => 'EXPIRED'
          ]);
          break;

        case 'FAILED':
          $pembayaran_user->update([
            'status' => 'FAILED'
          ]);
          break;

        default:
          return response()->json([
            'success' => false,
            'message' => 'Status tidak valid'
          ]);
      }

      return response()->json([
        'success' => true,
        'message' => 'Pembayaran berhasil'
      ]);
    }
  }
}
