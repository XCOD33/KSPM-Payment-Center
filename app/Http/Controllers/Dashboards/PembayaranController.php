<?php

namespace App\Http\Controllers\Dashboards;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Models\PembayaranUser;
use App\Models\Position;
use App\Models\PositionPembayaran;
use App\Models\RolePembayaran;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Nekoding\Tripay\Networks\HttpClient;
use Nekoding\Tripay\Signature;
use Nekoding\Tripay\Tripay;
use Ramsey\Uuid\Uuid;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class PembayaranController extends Controller
{
    public function index()
    {
        return view('dashboard.pembayaran.index');
    }

    public function get_pembayaran()
    {
        $pembayarans = Pembayaran::with('user')->get();

        foreach ($pembayarans as $pembayaran) {
            if (now() > $pembayaran->expired_at) {
                $pembayaran->status = 'inactive';
                $pembayaran->save();
            }
        }

        $data = DataTables::of($pembayarans)
            ->addIndexColumn()
            ->addColumn('nominal', function ($pembayaran) {
                return 'Rp ' . number_format($pembayaran->nominal, 0, ',', '.');
            })
            ->addColumn('status', function ($pembayaran) {
                if ($pembayaran->status == 'active') {
                    return 'Aktif';
                } else if ($pembayaran->status == 'inactive') {
                    return 'Tidak Aktif';
                }
            })
            ->addColumn('created_by', function ($q) {
                return $q->user->name . ' - ' . $q->user->position->name;
            })
            ->addColumn('created_at', function ($q) {
                return $q->created_at->format('d-M-Y H:i');
            })
            ->addColumn('expired_at', function ($q) {
                return $q->expired_at == null ? '-' : Carbon::createFromFormat('Y-m-d H:i:s', $q->expired_at)->format('d-M-Y H:i');
            })
            ->addColumn('url', function ($q) {
                return url('/dashboard/pembayaran/' . $q->url);
            })
            ->toJson();

        return $data;
    }

    public function get_pembayaran_user(Request $request)
    {
        try {
            $rolePembayaran = Pembayaran::with('role_pembayarans')->where('uuid', $request->uuid)->first()->role_pembayarans->pluck('role_id');
            $pembayaran = Pembayaran::where('uuid', $request->uuid)->first();
            $users = User::with(['roles'])
                ->whereHas('roles', function ($query) use ($rolePembayaran) {
                    $query->whereIn('id', $rolePembayaran);
                })
                ->get();


            $data = DataTables::of($users)
                ->addIndexColumn()
                ->addColumn('created_at', function ($user) use ($pembayaran) {
                    return $user->pembayaran_users->where('pembayaran_id', $pembayaran->id)->first() == null ? '-' : $user->pembayaran_users->where('pembayaran_id', $pembayaran->id)->first()->created_at->format('d-M-Y H:i');
                })
                ->addColumn('merchant_ref', function ($user) use ($pembayaran) {
                    return $user->pembayaran_users->where('pembayaran_id', $pembayaran->id)->first() == null ? '-' : $user->pembayaran_users->where('pembayaran_id', $pembayaran->id)->first()->uuid;
                })
                ->addColumn('status', function ($user) use ($pembayaran) {
                    if ($user->pembayaran_users->where('pembayaran_id', $pembayaran->id)->first() == null) {
                        return '-';
                    } else {
                        if ($user->pembayaran_users->where('pembayaran_id', $pembayaran->id)->first()->status == 'UNPAID') {
                            return 'Belum Bayar';
                        } else if ($user->pembayaran_users->where('pembayaran_id', $pembayaran->id)->first()->status == 'PAID') {
                            return 'Sudah Bayar';
                        } else if ($user->pembayaran_users->where('pembayaran_id', $pembayaran->id)->first()->status == 'EXPIRED') {
                            return 'Kadaluarsa';
                        } else if ($user->pembayaran_users->where('pembayaran_id', $pembayaran->id)->first()->status == 'FAILED') {
                            return 'Gagal';
                        }
                    }
                })
                ->addColumn('roles', function ($user) {
                    $roles = '';
                    foreach ($user->roles as $role) {
                        $roles .= $role->name . ', ';
                    }
                    return strtoupper(rtrim($roles, ', '));
                })
                ->addColumn('payment_method', function ($user) use ($pembayaran) {
                    if ($user->pembayaran_users->where('pembayaran_id', $pembayaran->id)->first() == null || $user->pembayaran_users->where('pembayaran_id', $pembayaran->id)->first()->payment_method == null) {
                        return '-';
                    } else {
                        return $user->pembayaran_users->where('pembayaran_id', $pembayaran->id)->first()->payment_method;
                    }
                })
                ->addColumn('total_fee', function ($user) use ($pembayaran) {
                    if ($user->pembayaran_users->where('pembayaran_id', $pembayaran->id)->first() == null || $user->pembayaran_users->where('pembayaran_id', $pembayaran->id)->first()->total_fee == null) {
                        return 0;
                    } else {
                        return $user->pembayaran_users->where('pembayaran_id', $pembayaran->id)->first()->total_fee;
                    }
                })
                ->addColumn('subtotal', function ($user) use ($pembayaran) {
                    if ($user->pembayaran_users->where('pembayaran_id', $pembayaran->id)->first() == null || $user->pembayaran_users->where('pembayaran_id', $pembayaran->id)->first()->subtotal == null) {
                        return 0;
                    } else {
                        return $user->pembayaran_users->where('pembayaran_id', $pembayaran->id)->first()->subtotal;
                    }
                })
                ->addColumn('total', function ($user) use ($pembayaran) {
                    if ($user->pembayaran_users->where('pembayaran_id', $pembayaran->id)->first() == null || $user->pembayaran_users->where('pembayaran_id', $pembayaran->id)->first()->total == null) {
                        return 0;
                    } else {
                        return $user->pembayaran_users->where('pembayaran_id', $pembayaran->id)->first()->total;
                    }
                })
                ->toJson();

            return $data;
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage(),
            ]);
        }
    }

    public function detail(Request $request)
    {
        $pembayaran = Pembayaran::where('uuid', $request->uuid)->first();
        $pembayaran->created_by_name = $pembayaran->user->name . ' - ' . $pembayaran->user->position->name;
        $pembayaran->expired_at = Carbon::createFromFormat('Y-m-d H:i:s', $pembayaran->expired_at)->format('d-m-Y H:i');
        $pembayaran->roles = RolePembayaran::where('pembayaran_id', $pembayaran->id)->with('role')->get()->pluck('role.id');
        return response()->json([
            'status' => 'success',
            'data' => $pembayaran,
        ]);
    }

    public function bayar($id)
    {
        $rolePembayaran = RolePembayaran::with('pembayaran')->where('role_id', auth()->user()->roles->pluck('id')->first())->where('pembayaran_id', Pembayaran::where('url', $id)->first()->id)->first();

        if (empty($rolePembayaran)) {
            if (auth()->user()->roles->pluck('name')->first() == 'super-admin') {
                $rolePembayaran = RolePembayaran::with('pembayaran')->where('pembayaran_id', Pembayaran::where('url', $id)->first()->id)->first();
            } else {
                return \abort(403, 'Anda tidak memiliki akses ke pembayaran ini');
            }
        }

        $pembayaran = $rolePembayaran->pembayaran;

        if ($pembayaran->status == 'inactive' || now() > $pembayaran->expired_at) {
            return \abort(403, 'Pembayaran tidak aktif');
        }

        $pembayaranUser = auth()->user()->pembayaran_users()->where('pembayaran_id', $pembayaran->id)->first();

        return view('dashboard.pembayaran.bayar', [
            'pembayaran' => $pembayaran,
            'pembayaranUser' => $pembayaranUser,
        ]);
    }

    public function channel()
    {
        $tripay = new Tripay(new HttpClient(config('app.tripay_api_key')));
        $res = $tripay->getChannelPembayaran();

        return response()->json([
            'status' => 'success',
            'data' => $res,
        ]);
    }

    public function bayar_post(Request $request)
    {
        $pembayaran = Pembayaran::where('uuid', $request->uuid)->firstOrFail();

        if ($pembayaran->status == 'inactive' || now() > $pembayaran->expired_at) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pembayaran tidak aktif',
            ]);
        }

        $merchantRef = Uuid::uuid4()->toString();

        $data = [
            'method' => $request->channel,
            'merchant_ref' => $merchantRef,
            'amount' => $pembayaran->nominal,
            'customer_name' => auth()->user()->name,
            'customer_email' => auth()->user()->email,
            'customer_phone' => auth()->user()->phone,
            'order_items' => [
                [
                    'sku' => $pembayaran->uuid,
                    'name' => $pembayaran->name,
                    'price' => $pembayaran->nominal,
                    'quantity' => 1,
                    'product_url' => null,
                    'image_url' => null,
                ]
            ],
            'return_url' => url('/dashboard/pembayaran/' . $pembayaran->url),
            'expired_time' => now()->addHours(24)->timestamp,
            'signature' => Signature::generate($merchantRef . $pembayaran->nominal),
        ];

        $pembayaranUser = PembayaranUser::where('pembayaran_id', $pembayaran->id)->where('user_id', auth()->user()->id)->first();
        if ($pembayaranUser == null) {
            PembayaranUser::create([
                'pembayaran_id' => $pembayaran->id,
                'user_id' => auth()->user()->id,
                'status' => 'UNPAID',
                'subtotal' => $pembayaran->nominal,
                'uuid' => $merchantRef,
            ]);
        } else {
            $pembayaranUser->update([
                'uuid' => $merchantRef,
            ]);
        }


        $tripay = new Tripay(new HttpClient(config('app.tripay_api_key')));
        $res = $tripay->createTransaction($data, Tripay::CLOSE_TRANSACTION)->getResponse();

        return response()->json([
            'status' => 'success',
            'message' => 'Anda akan dialihkan ke halaman pembayaran',
            'data' => $res,
        ]);
    }

    public function callback(Request $request)
    {
        $callbackSignature = $request->server('HTTP_X_CALLBACK_SIGNATURE');
        $json = $request->getContent();
        $signature = hash_hmac('sha256', $json, config('app.tripay_private_key'));

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
            $invoice = PembayaranUser::where('uuid', $invoiceId)
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

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|min:3|max:255',
                'nominal' => 'required|integer',
                'expired_at' => 'required|after_or_equal:today',
                'description' => 'required|min:3',
                'status' => 'required|in:active,inactive',
                'created_by' => 'required',
                'roles' => 'required',
            ]);

            Pembayaran::create([
                'name' => $request->name,
                'nominal' => $request->nominal,
                'expired_at' => Carbon::createFromFormat('d-m-Y H:i', $request->expired_at)->format('Y-m-d H:i:s'),
                'description' => $request->description,
                'status' => $request->status,
                'created_by' => User::where('uuid', $request->created_by)->first()->id,
            ]);

            foreach ($request->roles as $role) {
                RolePembayaran::create([
                    'pembayaran_id' => Pembayaran::latest()->first()->id,
                    'role_id' => Role::where('id', $role)->first()->id,
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Berhasil menambahkan data pembayaran',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage(),
            ]);
        }
    }

    public function edit(Request $request)
    {
        $pembayaran = Pembayaran::where('uuid', $request->uuid)->first();
        $pembayaran->created_by_name = $pembayaran->user->name . ' - ' . $pembayaran->user->position->name;
        $pembayaran->expired_at = Carbon::createFromFormat('Y-m-d H:i:s', $pembayaran->expired_at)->format('d-m-Y H:i');
        $pembayaran->roles = RolePembayaran::where('pembayaran_id', $pembayaran->id)->with('role')->get()->pluck('role.id');

        return response()->json([
            'status' => 'success',
            'data' => $pembayaran,
        ]);
    }

    public function update(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|min:3|max:255',
                'nominal' => 'required|integer',
                'expired_at' => 'required|after_or_equal:today',
                'description' => 'required|min:3',
                'status' => 'required|in:active,inactive',
                'roles' => 'required',
            ]);

            if ($request->status == 'active') {
                if (now() > Carbon::createFromFormat('d-m-Y H:i', $request->expired_at)) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Tanggal terakhir pembayaran tidak boleh kurang dari tanggal sekarang',
                    ]);
                }
            }

            $pembayaran = Pembayaran::where('uuid', $request->uuid)->first();

            $pembayaran->update([
                'name' => $request->name,
                'nominal' => $request->nominal,
                'expired_at' => Carbon::createFromFormat('d-m-Y H:i', $request->expired_at)->format('Y-m-d H:i:s'),
                'description' => $request->description,
                'status' => $request->status,
            ]);

            // Mengambil semua role yang terkait dengan pembayaran saat ini
            $existingRoles = RolePembayaran::where('pembayaran_id', $pembayaran->id)->pluck('role_id')->toArray();

            foreach ($request->roles as $role) {
                // Mengecek apakah peran saat ini ada dalam tabel RolePembayaran
                if (!in_array($role, $existingRoles)) {
                    // Jika tidak ada, buat entri baru
                    RolePembayaran::create([
                        'pembayaran_id' => $pembayaran->id,
                        'role_id' => Role::where('id', $role)->first()->id,
                    ]);
                } else {
                    // Jika ada, perbarui entri yang ada
                    RolePembayaran::where('pembayaran_id', $pembayaran->id)
                        ->where('role_id', $role)
                        ->update([
                            'pembayaran_id' => $pembayaran->id,
                            'role_id' => Role::where('id', $role)->first()->id,
                        ]);
                }
            }

            // Menghapus entri yang tidak ada dalam array saat ini
            $rolesToDelete = array_diff($existingRoles, $request->roles);
            RolePembayaran::where('pembayaran_id', $pembayaran->id)
                ->whereIn('role_id', $rolesToDelete)
                ->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Berhasil mengubah data pembayaran',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage(),
            ]);
        }
    }

    public function delete(Request $request)
    {
        $pembayaran = Pembayaran::where('uuid', $request->uuid)->first();
        $pembayaran->delete();

        $rolePembayaran = RolePembayaran::where('pembayaran_id', $pembayaran->id)->get();
        foreach ($rolePembayaran as $role) {
            $role->delete();
        }
        PembayaranUser::where('pembayaran_id', $pembayaran->id)->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil menghapus data pembayaran',
        ]);
    }

    public function edit_status(Request $request)
    {
        try {
            $pembayaran = Pembayaran::where('uuid', $request->uuid)->first();
            if (!$pembayaran) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pembayaran tidak ditemukan',
                ]);
            }

            $user = User::where('nim', $request->nim)->first();
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User tidak ditemukan',
                ]);
            }

            $pembayaranUser = PembayaranUser::where('pembayaran_id', $pembayaran->id)->where('user_id', $user->id)->first();
            if (!$pembayaranUser) {
                PembayaranUser::create([
                    'pembayaran_id' => $pembayaran->id,
                    'user_id' => $user->id,
                    'uuid' => Uuid::uuid4()->toString(),
                    'payment_method' => 'MANUAL',
                    'payment_method_code' => 'MANUAL',
                    'total_fee' => 0,
                    'subtotal' => $pembayaran->nominal,
                    'total' => $pembayaran->nominal,
                    'status' => 'PAID',
                ]);
            } else {
                $pembayaranUser->update([
                    'status' => 'PAID',
                    'payment_method' => 'MANUAL',
                    'payment_method_code' => 'MANUAL',
                    'total_fee' => 0,
                    'subtotal' => $pembayaran->nominal,
                    'total' => $pembayaran->nominal,
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Berhasil mengubah status pembayaran',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage(),
            ]);
        }
    }

    public function detail_delete(Request $request)
    {
        try {
            $pembayaranUser = PembayaranUser::where('uuid', $request->merchant_ref)->first();
            if (!$pembayaranUser) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pembayaran tidak ditemukan',
                ]);
            }

            $pembayaranUser->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Berhasil menghapus pembayaran',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage(),
            ]);
        }
    }
}
