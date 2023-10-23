<?php

namespace App\Http\Controllers\Dashboards;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Models\PembayaranUser;
use App\Models\Position;
use App\Models\PositionPembayaran;
use App\Models\User;
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
            ->toJson();

        return $data;
    }

    public function get_pembayaran_user(Request $request)
    {
        $positionPembayaran = Pembayaran::with('position_pembayarans')->where('uuid', $request->uuid)->first()->position_pembayarans->pluck('position_id');
        $user = User::whereIn('position_id', $positionPembayaran)->get();

        $data = DataTables::of($user)
            ->addIndexColumn()
            ->addColumn('position', function ($user) {
                return $user->position->name;
            })
            ->addColumn('created_at', function ($user) {
                return $user->pembayaran_users->first() == null ? '-' : $user->pembayaran_users->first()->created_at->format('d-M-Y H:i');
            })
            ->addColumn('status', function ($user) {
                if ($user->pembayaran_users->first() == null) {
                    return 'Belum Bayar';
                } else {
                    if ($user->pembayaran_users->first()->status == 'full') {
                        return 'Lunas';
                    } else if ($user->pembayaran_users->first()->status == 'partial') {
                        return 'Cicilan';
                    }
                }
            })
            ->toJson();

        return $data;
    }

    public function detail(Request $request)
    {
        $pembayaran = Pembayaran::where('uuid', $request->uuid)->first();
        $pembayaran->created_by_name = $pembayaran->user->name . ' - ' . $pembayaran->user->position->name;
        $pembayaran->expired_at = Carbon::createFromFormat('Y-m-d H:i:s', $pembayaran->expired_at)->format('d-m-Y H:i');
        $pembayaran->positions = PositionPembayaran::where('pembayaran_id', $pembayaran->id)->with('position')->get()->pluck('position.uuid');
        return response()->json([
            'status' => 'success',
            'data' => $pembayaran,
        ]);
    }

    public function bayar($id)
    {
        $positionPembayaran = PositionPembayaran::with('pembayaran')->where('position_id', auth()->user()->position_id)->where('pembayaran_id', Pembayaran::where('url', $id)->first()->id)->firstOrFail();
        $pembayaran = $positionPembayaran->pembayaran;

        if ($pembayaran->status == 'inactive' || now() > $pembayaran->expired_at) {
            return \abort(403, 'Pembayaran tidak aktif');
        }

        $pembayaranUser = auth()->user()->pembayaran_users()->where('pembayaran_id', $pembayaran->id)->first();

        return view('dashboard.pembayaran.bayar', [
            'pembayaran' => $pembayaran,
            'pembayaranUser' => $pembayaranUser,
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

        PembayaranUser::create([
            'pembayaran_id' => $pembayaran->id,
            'user_id' => auth()->user()->id,
            'status' => 'full',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil melakukan pembayaran',
        ]);
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
                'positions' => 'required',
            ]);

            Pembayaran::create([
                'name' => $request->name,
                'nominal' => $request->nominal,
                'expired_at' => Carbon::createFromFormat('d-m-Y H:i', $request->expired_at)->format('Y-m-d H:i:s'),
                'description' => $request->description,
                'status' => $request->status,
                'created_by' => User::where('uuid', $request->created_by)->first()->id,
            ]);

            foreach ($request->positions as $position) {
                PositionPembayaran::create([
                    'pembayaran_id' => Pembayaran::latest()->first()->id,
                    'position_id' => Position::where('uuid', $position)->first()->id,
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
        $pembayaran->positions = PositionPembayaran::where('pembayaran_id', $pembayaran->id)->with('position')->get()->pluck('position.uuid');
        return response()->json([
            'status' => 'success',
            'data' => $pembayaran,
        ]);
    }

    public function update(Request $request)
    {
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

        foreach (PositionPembayaran::where('pembayaran_id', $pembayaran->id)->get() as $position_pembayaran) {
            $position_pembayaran->delete();
        }

        foreach ($request->positions as $position) {
            PositionPembayaran::create([
                'pembayaran_id' => $pembayaran->id,
                'position_id' => Position::where('uuid', $position)->first()->id,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil mengubah data pembayaran',
        ]);
    }

    public function delete(Request $request)
    {
        $pembayaran = Pembayaran::where('uuid', $request->uuid)->first();
        $pembayaran->delete();

        $positionPembayarans = PositionPembayaran::where('pembayaran_id', $pembayaran->id)->get();
        foreach ($positionPembayarans as $position_pembayaran) {
            $position_pembayaran->delete();
        }
        PembayaranUser::where('pembayaran_id', $pembayaran->id)->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil menghapus data pembayaran',
        ]);
    }
}
