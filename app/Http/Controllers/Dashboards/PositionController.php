<?php

namespace App\Http\Controllers\Dashboards;

use App\Http\Controllers\Controller;
use App\Models\Position;
use App\Models\User;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class PositionController extends Controller
{
    public function index()
    {
        return view('dashboard.manage.position');
    }

    public function get_position()
    {
        $position = Position::all();

        $data = DataTables::of($position)
            ->addIndexColumn()
            ->addColumn('total_users', function ($q) {
                return $q->users->count();
            })
            ->toJson();

        return $data;
    }

    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:positions,name',
        ]);

        Position::create([
            'name' => $request->name,
        ]);

        return redirect()->back()->with('success', 'Berhasil menambahkan role baru');
    }

    public function view(Request $request)
    {
        $position = Position::findOrFail($request->id);

        return response()->json([
            'position' => $position,
        ]);
    }

    public function view_position(Request $request)
    {
        $selectedPosition = Position::where('id', $request->id)->with('users')->get();

        $data = DataTables::of($selectedPosition->first()->users)
            ->addIndexColumn()
            ->toJson();

        return $data;
    }

    public function edit(Request $request)
    {
        $position = Position::findOrFail($request->id);

        return response()->json([
            'position' => $position,
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:positions,name,' . $request->id,
        ]);

        $position = Position::find($request->id);

        $position->update([
            'name' => $request->name,
        ]);

        return redirect()->back()->with('success', 'Berhasil mengubah Jabatan');
    }

    public function delete(Request $request)
    {
        $position = Position::findOrFail($request->id);

        $users = User::where('position_id', $position->id)->get();
        if ($users->count() > 0) {
            foreach ($users as $user) {
                $user->position_id = null;
                $user->save();
            }
        }

        $position->delete();

        return redirect()->back()->with('success', 'Berhasil menghapus Jabatan');
    }

    public function add_user(Request $request)
    {
        $user = User::where('uuid', $request->new_user)->first();
        $position = Position::where('uuid', $request->position)->first();

        $user->position_id = $position->id;
        $user->save();

        return response()->json([
            'user' => $user,
        ]);
    }

    public function remove_user(Request $request)
    {
        $user = User::where('uuid', $request->id)->first();

        $user->update([
            'position_id' => null,
        ]);

        return response()->json([
            'user' => User::where('position_id', null)->get()
        ]);
    }
}
