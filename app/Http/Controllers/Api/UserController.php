<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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

    return response()->json([
      'success' => true,
      'message' => 'Detail User : ' . $user->name,
      'data' => $user,
    ]);
  }

  public function change_password(Request $request)
  {
    $request->validate([
      'old_password' => 'required|min:8|max:255',
      'new_password' => 'required|min:8|max:255',
    ], [
      'old_password.required' => 'Password lama harus diisi',
      'old_password.min' => 'Password lama minimal 8 karakter',
      'old_password.max' => 'Password lama maksimal 255 karakter',
      'new_password.required' => 'Password baru harus diisi',
      'new_password.min' => 'Password baru minimal 8 karakter',
      'new_password.max' => 'Password baru maksimal 255 karakter'
    ]);

    $user = User::where('id', $this->user->id)->first();

    if (!Hash::check($request->old_password, $user->password)) {
      return response()->json([
        'success' => false,
        'message' => 'Password lama salah'
      ], 401);
    }

    if (Hash::check($request->new_password, $user->password)) {
      return response()->json([
        'success' => false,
        'message' => 'Password baru tidak boleh sama dengan password lama'
      ], 401);
    }

    $user->password = Hash::make($request->new_password);
    $user->save();
    return response()->json([
      'success' => true,
      'message' => 'Password berhasil diubah'
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
