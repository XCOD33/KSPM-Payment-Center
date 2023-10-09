<?php

namespace App\Http\Controllers;

use App\Exports\UsersExport;
use App\Exports\PositionsExport;
use App\Models\User;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\UsersImport;

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
            'name' => $user->name,
            'member_id' => $user->member_id,
            'role' => $user->roles->first()->name,
            'position' => $user->position->id,
            'year' => $user->year,
            'uuid' => $user->uuid,
        ]);
    }

    public function create_user(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'member_id' => 'required|string|unique:users,member_id|min:9|max:9',
            'password' => 'required|string',
            'role' => 'required|string',
            'position' => 'required|integer',
            'year' => 'required|integer',
        ]);

        $user = User::create([
            'name' => $request->name,
            'member_id' => $request->member_id,
            'password' => bcrypt($request->password),
            'position_id' => $request->position,
            'year' => $request->year,
        ]);

        $user->assignRole($request->role);

        return redirect(route('manage.users.index'))->with('success', 'Berhasil menambahkan user!');
    }

    public function update_user(Request $request)
    {
        $request->validate([
            'nameEdit' => 'required|string',
            'roleEdit' => 'required|string',
            'passwordEdit' => 'nullable|string|min:8|max:64',
            'positionEdit' => 'required',
            'yearEdit' => 'required',
        ]);

        $user = User::where('uuid', $request->uuid)->firstOrFail();

        $user->update([
            'name' => $request->nameEdit,
            'password' => !empty($request->passwordEdit) ? bcrypt($request->passwordEdit) : $user->password,
            'position_id' => (int)$request->positionEdit,
            'year' => (int)$request->yearEdit,
        ]);

        $user->removeRole($user->roles->first()->name);
        $user->assignRole($request->roleEdit);

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

    public function download_excel()
    {
        return Excel::download(new class implements \Maatwebsite\Excel\Concerns\WithMultipleSheets
        {
            public function sheets(): array
            {
                return [
                    'Data Pengguna' => new UsersExport,
                    'Posisi Tersedia' => new PositionsExport,
                ];
            }
        }, 'download-format-tambah-anggota.xlsx', \Maatwebsite\Excel\Excel::XLSX);
    }
}
