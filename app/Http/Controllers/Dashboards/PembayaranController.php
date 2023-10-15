<?php

namespace App\Http\Controllers\Dashboards;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use Carbon\Carbon;
use Illuminate\Http\Request;
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
            ->toJson();

        return $data;
    }

    public function edit(Request $request)
    {
        $pembayaran = Pembayaran::where('uuid', $request->uuid)->first();
        $pembayaran->created_by_name = $pembayaran->user->name . ' - ' . $pembayaran->user->position->name;
        $pembayaran->expired_at = Carbon::createFromFormat('Y-m-d H:i:s', $pembayaran->expired_at)->format('d-m-Y H:i');
        return response()->json([
            'status' => 'success',
            'data' => $pembayaran,
        ]);
    }

    public function update(Request $request)
    {
        $pembayaran = Pembayaran::where('uuid', $request->uuid)->first();

        $pembayaran->update([
            'name' => $request->name,
            'nominal' => $request->nominal,
            'expired_at' => Carbon::createFromFormat('d-m-Y H:i', $request->expired_at)->format('Y-m-d H:i:s'),
            'description' => $request->description,
            'status' => $request->status,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil mengubah data pembayaran',
        ]);
    }
}
