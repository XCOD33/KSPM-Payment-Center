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
use App\Models\Position;

class DashboardController extends Controller
{
    public function index()
    {
        return view('dashboard.index');
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
            ->addColumn('name', function ($q) {
                return $q->name;
            })
            ->addColumn('member_id', function ($q) {
                return $q->member_id;
            })
            ->addColumn('year', function ($q) {
                return $q->year;
            })
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
        ]);
    }

    public function create_user(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'member_id' => 'required|string|unique:users,member_id|min:9|max:9',
            'password' => 'required|string',
            'nim' => 'required|string|unique:users,nim|min:10|max:10',
            'position' => 'integer',
            'year' => 'required|integer',
        ]);

        User::create($request->all());

        return redirect(route('manage.users.index'))->with('success', 'Berhasil menambahkan user!');
    }

    public function update_user(Request $request)
    {
        $request->validate([
            'nameEdit' => 'required|string',
            'passwordEdit' => 'nullable|string|min:8|max:64',
            'positionEdit' => 'required',
            'yearEdit' => 'required',
        ]);

        $user = User::where('uuid', $request->uuid)->firstOrFail();
        $positions = Position::all();
        if (!empty($request->positionEdit)) {
            foreach ($positions as $position) {
                if ($position->id == $request->positionEdit && $position->can_duplicate == 'no') {
                    return back()->with('error', 'Posisi yang dipilih tidak dapat diubah karena posisi ' . $position->name . ' tidak boleh lebih dari satu!');
                }
            }
        }

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
            'password' => $request->passwordEdit,
            'position_id' => $request->positionEdit,
            'year' => $request->yearEdit,
        ]);

        return redirect(route('manage.users.index'))->with('success', 'Berhasil mengubah user!');
    }

    public function delete_user(Request $request)
    {
        $user = User::where('uuid', $request->uuid)->firstOrFail();

        $user->removeRole($user->roles->first()->name);

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
}
