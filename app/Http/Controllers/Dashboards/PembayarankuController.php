<?php

namespace App\Http\Controllers\Dashboards;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class PembayarankuController extends Controller
{
    public function index()
    {
        return view('dashboard.pembayaranku.index');
    }

    public function pembayarans()
    {
        $pembayarans = Pembayaran::with('role_pembayarans')->with('pembayaran_users')->whereHas('role_pembayarans', function ($query) {
            $query->where('role_id', auth()->user()->roles->pluck('id')->first());
        })->get();

        $data = DataTables::of($pembayarans)
            ->addIndexColumn()
            ->addColumn('created_by', function ($q) {
                return $q->user->name;
            })
            ->addColumn('created_at', function ($q) {
                return $q->created_at->format('d-M-Y H:i');
            })
            ->addColumn('expired_at', function ($q) {
                return Carbon::parse($q->expired_at)->format('d-M-Y H:i');
            })
            ->addColumn('status', function ($q) {
                return $q->status == 'active' ? 'Aktif' : 'Tidak Aktif';
            })
            ->addColumn('status_payment', function ($q) {
                $pembayaranUsers = $q->pembayaran_users->where('user_id', auth()->user()->id)->first();
                if (empty($pembayaranUsers)) {
                    return 'UNPAID';
                } else {
                    return $pembayaranUsers->status;
                }
            })
            ->toJson();

        return $data;
    }

    public function invoice(Request $request)
    {
        try {
            $pembayaran = Pembayaran::with(['pembayaran_users' => function ($q) {
                $q->where('user_id', auth()->user()->id)->with('user');
            }])->where('uuid', $request->uuid)->first();

            $pembayaran->pembayaran_users[0]->created_at = $pembayaran->pembayaran_users[0]->created_at->format('d-M-Y H:i');

            if (empty($pembayaran)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data tidak ditemukan'
                ]);
            }

            return response()->json([
                'status' => 'success',
                'data' => $pembayaran
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ]);
        }
    }
}
