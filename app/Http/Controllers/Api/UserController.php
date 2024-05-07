<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth:sanctum')->except('login');
    $this->user = auth('sanctum')->user();
  }

  public function login(Request $request)
  {
    $request->validate([
      'nim' => 'required|min:1|max:255',
      'password' => 'required|min:8|max:255'
    ], [
      'nim.required' => 'NIM harus diisi',
      'nim.min' => 'NIM minimal 10 karakter',
      'nim.max' => 'NIM maksimal 255 karakter',
      'password.required' => 'Password harus diisi',
      'password.min' => 'Password minimal 8 karakter',
      'password.max' => 'Password maksimal 255 karakter'
    ]);

    $user = User::where('nim', $request->nim)->first();

    if (!$user || !password_verify($request->password, $user->password)) {
      return response()->json([
        'message' => 'NIM atau password salah'
      ], 401);
    }

    if (auth('sanctum')->check()) {
      $this->user->tokens()->delete();
    }

    $token = $user->createToken('api-android')->plainTextToken;

    return response()->json([
      'success' => true,
      'message' => 'Login berhasil',
      'data' => [
        'token' => $token,
        'nama' => $user->name,
      ]
    ]);
  }

  public function detail()
  {
    $user = User::where('id', $this->user->id)
      ->with(['roles', 'position'])
      ->first();

    // $pembayarans = [];
    // foreach ($user->roles as $role) {
    //   $role_id = $role->id;
    //   $pembayarans = Pembayaran::whereHas('role_pembayarans', function ($query) use ($role_id) {
    //     $query->where('role_id', $role_id);
    //   })->get();
    // }

    return response()->json([
      'success' => true,
      'message' => 'Detail User : ' . $user->name,
      'data' => $user,
    ]);
  }

  public function logout(Request $request)
  {
    $token = str_replace('Bearer ', '', $request->header('Authorization'));

    if (!$token) {
      return response()->json([
        'success' => false,
        'message' => 'Token tidak ditemukan'
      ], 401);
    }

    if (!auth('sanctum')->check()) {
      return response()->json([
        'success' => false,
        'message' => 'Unauthorized'
      ], 401);
    }

    $request->validate(
      [
        'nim' => 'required|min:1|max:255'
      ],
      [
        'nim.required' => 'NIM harus diisi',
        'nim.min' => 'NIM minimal 10 karakter',
        'nim.max' => 'NIM maksimal 255 karakter'
      ]
    );

    $user = User::where('nim', $request->nim)->first();
    if (!$user) {
      return response()->json([
        'message' => 'NIM tidak ditemukan'
      ], 404);
    }
    $user->tokens()->delete();

    return response()->json([
      'success' => true,
      'message' => 'Logout berhasil'
    ]);
  }
}
