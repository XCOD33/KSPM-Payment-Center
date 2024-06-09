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
use Ramsey\Uuid\Uuid;
use Symfony\Component\Uid\Uuid as UidUuid;

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
                    'uuid' => Uuid::uuid4(),
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

        $merchant_ref = Uuid::uuid4();

        $dataToTripay = [
            'method' => $request->payment_code,
            'merchant_ref' => $merchant_ref,
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
            'signature' => Signature::generate($merchant_ref . $pembayaran->nominal)
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
        $signature = hash_hmac('sha256', $json, env('TRIPAY_PRIVATE_KEY'));

        if ($signature !== (string) $callbackSignature) {
            return Response::json([
                'success' => false,
                'message' => 'Invalid signature',
            ]);
        }

        if ('payment_status' !== (string) $request->server('HTTP_X_CALLBACK_EVENT')) {
            return Response::json([
                'success' => false,
                'message' => 'Unrecognized callback event, no action was taken',
            ]);
        }

        $data = json_decode($json);

        if (JSON_ERROR_NONE !== json_last_error()) {
            return Response::json([
                'success' => false,
                'message' => 'Invalid data sent by tripay',
            ]);
        }

        $invoiceId = $data->merchant_ref;
        $status = strtoupper((string) $data->status);

        if ($data->is_closed_payment === 1) {
            $invoice = PembayaranUser::where('invoice_id', $invoiceId)
                ->where('status', '=', 'UNPAID')
                ->first();

            if (!$invoice) {
                return Response::json([
                    'success' => false,
                    'message' => 'No invoice found or already paid: ' . $invoiceId,
                ]);
            }

            switch ($status) {
                case 'PAID':
                    $invoice->update(['status' => 'PAID', 'payment_method' => $data->payment_method, 'payment_method_code' => $data->payment_method_code, 'total_fee' => $data->total_fee, 'total' => $data->total_amount]);

                    $response = Http::withHeaders([
                        'Authorization' => env('FONNTE')
                    ])->post('https://api.fonnte.com/send', [
                        'target' => $invoice->user->phone,
                        'message' => "Halo " . $invoice->user->name . ",\n\nSalam sejahtera. Kami ingin memberitahukan bahwa pembayaran untuk *" . $invoice->pembayaran->name . "* telah kami terima.\n\nBerikut adalah rincian pembayaran:\n\n- Jumlah Pembayaran : Rp" . number_format($invoice->subtotal, 0, ',', '.') . "\n- Nomor Invoice : " . $invoice->invoice_id . "\n- Biaya Admin : Rp" . number_format($invoice->total_fee, 0, ',', '.') . "\n- Total Pembayaran : Rp" . number_format($invoice->total, 0, ',', '.') . "\n- Cetak Bukti Pembayaran : " . url('dashboard/pembayaranku/view-invoice', $invoice->invoice_id) . "\n\nTerima kasih telah melakukan pembayaran. Semoga harimu menyenangkan.\n\nHormat Kami,\nTim Bendahara KSPM UTY",
                    ]);
                    if ($response->successful()) {
                        $response = $response->json();
                        if ($response['status'] == false) {
                            return Response::json([
                                'success' => false,
                                'message' => 'Gagal mengirim SMS',
                            ]);
                        };
                    } else {
                        return Response::json([
                            'success' => false,
                            'message' => 'Gagal mengirim SMS',
                        ]);
                    }
                    break;

                case 'EXPIRED':
                    $invoice->update(['status' => 'EXPIRED']);
                    break;

                case 'FAILED':
                    $invoice->update(['status' => 'FAILED']);
                    break;

                default:
                    return Response::json([
                        'success' => false,
                        'message' => 'Unrecognized payment status',
                    ]);
            }

            return Response::json(['success' => true]);
        }
    }
}
