<?php

namespace App\Http\Controllers\Dashboards;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class RolesController extends Controller
{
    public function manage_roles_get()
    {
        return view('dashboard.manage.role');
    }

    public function get_roles()
    {
        $roles = Role::withCount('users')->get();

        $data = DataTables::of($roles)
            ->addIndexColumn()
            ->addColumn('total_users', function ($q) {
                return $q->users_count;
            })
            ->toJson();

        return $data;
    }

    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name',
        ]);

        Role::create([
            'name' => $request->name,
        ]);

        return redirect()->back()->with('success', 'Berhasil menambahkan role baru');
    }

    public function view(Request $request)
    {
        $role = Role::findOrFail($request->id);

        return response()->json([
            'role' => $role,
        ]);
    }

    public function view_roles(Request $request)
    {
        $selectedRole = Role::where('id', $request->id)->with('users')->get();

        $data = DataTables::of($selectedRole->first()->users)
            ->addIndexColumn()
            ->toJson();

        return $data;
    }

    public function edit(Request $request)
    {
        $role = Role::find($request->id);

        return response()->json([
            'role' => $role,
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name,' . $request->id,
        ]);

        $role = Role::find($request->id);

        $role->update([
            'name' => $request->name,
        ]);

        return redirect()->back()->with('success', 'Berhasil mengubah role');
    }

    public function delete(Request $request)
    {
        $role = Role::find($request->id);

        $role->delete();

        return redirect()->back()->with('success', 'Berhasil menghapus role');
    }

    public function add_user(Request $request)
    {
        $user = User::where('uuid', $request->new_user)->firstOrFail();

        $user->assignRole($request->role);

        return redirect()->back()->with('success', 'Berhasil menambahkan user ke role');
    }

    public function remove_user(Request $request)
    {
        $user = User::where('uuid', $request->id)->firstOrFail();

        $user->roles()->detach();

        return redirect()->back()->with('success', 'Berhasil menghapus user dari role');
    }
}
