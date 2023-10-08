<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

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
        $users = User::with('roles')->get();

        $data = DataTables::of($users)
            ->addIndexColumn()
            ->addColumn('name', function ($q) {
                return $q->name;
            })
            ->addColumn('member_id', function ($q) {
                return $q->member_id;
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
        ]);

        $user = User::create([
            'name' => $request->name,
            'member_id' => $request->member_id,
            'password' => bcrypt($request->password),
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
        ]);

        $user = User::where('uuid', $request->uuid)->firstOrFail();

        $user->update([
            'name' => $request->nameEdit,
            'password' => !empty($request->passwordEdit) ? bcrypt($request->passwordEdit) : $user->password,
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
}
