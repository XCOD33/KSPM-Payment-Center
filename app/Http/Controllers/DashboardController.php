<?php

namespace App\Http\Controllers;

use App\Exports\UsersExport;
use App\Exports\PositionsExport;
use App\Exports\UsersExportAll;
use App\Models\User;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\UsersImport;
use App\Models\Pembayaran;
use App\Models\PembayaranUser;
use App\Models\Position;
use App\Models\RolePembayaran;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\DataTables as DataTablesDataTables;

class DashboardController extends Controller
{
    public function index()
    {
        if (auth()->user()->hasRole('super-admin')) {
            $total_users = User::count();
            $total_roles = Role::count();
            $total_positions = Position::count();

            $total_pembayarans = Pembayaran::count();
            $role_pembayarans = RolePembayaran::all();
            $total_users_belum_bayar = 0;
            $total_users_bayar = PembayaranUser::where('status', 'PAID')->count();
            $total_uang_belum_terkumpul = 0;
            $total_uang_terkumpul = PembayaranUser::where('status', 'PAID')->sum('subtotal');
            // Menggunakan perulangan foreach untuk mengiterasi setiap objek Pembayaran
            foreach (Pembayaran::all() as $pembayaran) {
                // Mengambil daftar role_id dari objek RolePembayaran yang terkait dengan Pembayaran saat ini
                $rolePembayaran = $pembayaran->role_pembayarans->pluck('role_id');

                // Mengambil daftar User yang belum melakukan pembayaran berdasarkan role_id yang terkait dengan Pembayaran saat ini
                $usersBelumBayar = User::whereHas('roles', function ($query) use ($rolePembayaran) {
                    $query->whereIn('id', $rolePembayaran);
                })->get();

                // Memfilter daftar User yang belum melakukan pembayaran berdasarkan Pembayaran saat ini
                $usersBelumBayar = $usersBelumBayar->filter(function ($user) use ($pembayaran) {
                    $pembayaranUser = $user->pembayaran_users->where('pembayaran_id', $pembayaran->id)->first();
                    return !$pembayaranUser || ($pembayaranUser && $pembayaranUser->pivot && $pembayaranUser->pivot->status != 'PAID');
                });

                // Menambahkan jumlah User yang belum melakukan pembayaran ke total_users_belum_bayar
                $total_users_belum_bayar += $usersBelumBayar->count();
                $total_uang_belum_terkumpul += $usersBelumBayar->count() * $pembayaran->nominal;
            }

            return view('dashboard.index', [
                'total_users' => $total_users,
                'total_roles' => $total_roles,
                'total_positions' => $total_positions,
                'total_pembayarans' => $total_pembayarans,
                'total_users_belum_bayar' => $total_users_belum_bayar,
                'total_users_bayar' => $total_users_bayar,
                'total_uang_belum_terkumpul' => $total_uang_belum_terkumpul,
                'total_uang_terkumpul' => $total_uang_terkumpul,
            ]);
        } else {
            $password_changed = 'true';
            if (Auth::attempt(['email' => auth()->user()->email, 'password' => 'password'])) {
                $password_changed = 'false';
                session()->flash('warning', 'Password anda masih default, silahkan ubah password anda sekarang!');
            }

            $pembayarans = Pembayaran::with(['role_pembayarans' => function ($query) {
                $query->where('role_id', auth()->user()->roles->pluck('id')->first());
            }])->with(['pembayaran_users' => function ($query) {
                $query->where('user_id', auth()->user()->id)->where('status', 'PAID');
            }])->get();

            $active_bill = 0;
            $paid_bill = 0;
            $total_active_bill = 0;
            $total_paid_bill = 0;
            foreach ($pembayarans as $pembayaran) {
                if ($pembayaran->role_pembayarans->count() != 0) {
                    if ($pembayaran->pembayaran_users->count() == 0) {
                        $active_bill++;
                        $total_active_bill += $pembayaran->nominal;
                    } else {
                        $paid_bill++;
                        $total_paid_bill += $pembayaran->nominal;
                    }
                }
            }

            return view('dashboard.index', [
                'active_bill' => $active_bill,
                'paid_bill' => $paid_bill,
                'total_active_bill' => $total_active_bill,
                'total_paid_bill' => $total_paid_bill,
                'password_changed' => $password_changed,
            ]);
        }
    }

    public function data(Request $request)
    {
        $pembayarans = Pembayaran::latest()->take(3);

        $data = DataTables::of($pembayarans)
            ->addIndexColumn()
            ->addColumn('created_by', function ($q) {
                return User::where('id', $q->created_by)->first()->name;
            })
            ->addColumn('tagihan', function ($q) {
                $rolePembayaran = Pembayaran::with('role_pembayarans')
                    ->where('uuid', $q->uuid)
                    ->first()
                    ->role_pembayarans
                    ->pluck('role_id');

                $users = User::with(['roles'])
                    ->whereHas('roles', function ($query) use ($rolePembayaran) {
                        $query->whereIn('id', $rolePembayaran);
                    })
                    ->get();

                return $users->count();
            })
            ->addColumn('terbayar', function ($q) {
                $rolePembayaran = Pembayaran::with('role_pembayarans')
                    ->where('uuid', $q->uuid)
                    ->first()
                    ->role_pembayarans
                    ->pluck('role_id');

                $users = User::with(['roles'])
                    ->whereHas('roles', function ($query) use ($rolePembayaran) {
                        $query->whereIn('id', $rolePembayaran);
                    })
                    ->whereHas('pembayaran_users', function ($query) {
                        $query->where('status', 'PAID');
                    })
                    ->get();

                return $users->count();
            })
            ->addColumn('sisa', function ($q) {
                $rolePembayaran = Pembayaran::with('role_pembayarans')
                    ->where('uuid', $q->uuid)
                    ->first()
                    ->role_pembayarans
                    ->pluck('role_id');

                $users = User::with(['roles'])
                    ->whereHas('roles', function ($query) use ($rolePembayaran) {
                        $query->whereIn('id', $rolePembayaran);
                    })
                    ->where(function ($query) {
                        $query->whereDoesntHave('pembayaran_users')
                            ->orWhereHas('pembayaran_users', function ($query) {
                                $query->where('status', '!=', 'PAID');
                            });
                    })
                    ->get();

                return $users->count();
            })
            ->toJson();

        return $data;
    }

    public function manage_users_get()
    {
        return view('dashboard.manage.user');
    }

    public function get_users()
    {
        $users = User::with('roles')->orderBy('year', 'DESC')->get();

        $data = DataTables::of($users)
            ->addIndexColumn()
            ->addColumn('position', function ($q) {
                return !empty($q->position->name) ? $q->position->name : 'Tidak ada';
            })
            ->addColumn('roles', function ($q) {
                return !empty($q->roles->first()->name) ? $q->roles->first()->name : 'Tidak ada';
            })
            ->toJson();

        return $data;
    }

    public function detail_user(Request $request)
    {
        $user = User::where('uuid', $request->uuid)->firstOrFail();

        return response()->json([
            'name' => $user->name ?? 'Tidak ada',
            'member_id' => $user->member_id ?? 'Tidak ada',
            'nim' => $user->nim ?? 'Tidak ada',
            'position' => $user->position->id ?? 'Tidak ada',
            'year' => $user->year ?? 'Tidak ada',
            'uuid' => $user->uuid ?? 'Tidak ada',
            'email' => $user->email ?? 'Tidak ada',
            'phone' => $user->phone ?? 'Tidak ada',
        ]);
    }

    public function create_user(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'member_id' => 'required|string|unique:users,member_id|min:9|max:9',
            'password' => 'required|string',
            'nim' => 'required|string|unique:users,nim|min:10|max:10',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|min:10|max:13',
            'position_id' => 'integer',
            'year' => 'required|integer',
        ]);

        User::create($request->all());


        return redirect(route('manage.users.index'))->with('success', 'Berhasil menambahkan user!');
    }

    public function update_user(Request $request)
    {
        $request->validate([
            'nameEdit' => 'required|string',
            'member_idEdit' => 'required|string|min:9|max:9',
            'nimEdit' => 'required|string|min:10|max:10',
            'emailEdit' => 'required|email',
            'phoneEdit' => 'required|string|min:10|max:13',
            'passwordEdit' => 'nullable|string|min:8|max:64',
            'positionEdit' => 'required',
            'yearEdit' => 'required',
        ]);

        $user = User::where('uuid', $request->uuid)->firstOrFail();
        // $positions = Position::all();
        // if (!empty($request->positionEdit)) {
        //     foreach ($positions as $position) {
        //         if ($position->id == $request->positionEdit && $position->can_duplicate == 'no') {
        //             return back()->with('error', 'Posisi yang dipilih tidak dapat diubah karena posisi ' . $position->name . ' tidak boleh lebih dari satu!');
        //         }
        //     }
        // }

        if (!empty($request->passwordEdit)) {
            $request->validate([
                'passwordEdit' => 'string|min:8|max:64',
            ]);
            $request->passwordEdit = bcrypt($request->passwordEdit);
        } else {
            $request->passwordEdit = $user->password;
        }
        $user->update([
            'name' => $request->nameEdit,
            'nim' => $request->nimEdit,
            'member_id' => $request->member_idEdit,
            'email' => $request->emailEdit,
            'phone' => $request->phoneEdit,
            'password' => $request->passwordEdit,
            'position_id' => $request->positionEdit,
            'year' => $request->yearEdit,
        ]);

        return redirect(route('manage.users.index'))->with('success', 'Berhasil mengubah user!');
    }

    public function delete_user(Request $request)
    {
        $user = User::where('uuid', $request->uuid)->firstOrFail();

        if ($user->roles->first() != null) {
            $user->removeRole($user->roles->first()->name);
        }

        if ($user->delete()) {
            return redirect(route('manage.users.index'))->with('success', 'Berhasil menghapus user!');
        }

        return redirect(route('manage.users.index'))->with('error', 'Gagal menghapus user!');
    }

    public function upload_excel(Request $request)
    {
        Excel::import(new UsersImport, $request->file('excel'));

        return redirect(route('manage.users.index'))->with('success', 'Berhasil menambahkan user!');
    }

    public function download_excel(Request $request)
    {
        if ($request->query('dl') == 'all') {
            return Excel::download(new UsersExportAll, 'daftar-users.xlsx');
        } else {
            // return Excel::download(new class implements \Maatwebsite\Excel\Concerns\WithMultipleSheets
            // {
            //     public function sheets(): array
            //     {
            //         return [
            //             'Data Pengguna' => new UsersExport,
            //             'Posisi Tersedia' => new PositionsExport,
            //         ];
            //     }
            // }, 'download-format-tambah-anggota.xlsx', \Maatwebsite\Excel\Excel::XLSX);
            return Excel::download(new UsersExport, 'download-format-tambah-anggota.xlsx');
        }
    }

    public function change_password(Request $request)
    {
        $request->validate([
            'old_password' => 'required|string|min:8|max:64',
            'password' => 'required|string|min:8|max:64',
            'password_confirmation' => 'required|string|min:8|max:64|same:password',
        ]);

        if (Auth::attempt(['email' => auth()->user()->email, 'password' => $request->old_password])) {
            if ($request->old_password == $request->password) {
                return redirect(route('dashboard'))->with('error', 'Password baru tidak boleh sama dengan password lama!');
            }
            $user = User::where('email', auth()->user()->email)->firstOrFail();
            $user->update([
                'password' => bcrypt($request->password),
            ]);

            return redirect(route('dashboard'))->with('success', 'Berhasil mengubah password!');
        } else {
            return redirect(route('dashboard'))->with('error', 'Password lama yang anda masukkan salah!');
        }
    }
}
