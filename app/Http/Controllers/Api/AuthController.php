<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // validasi inputan user
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|confirmed'
        ]);

        // jika ada yang salah
        if ($validator->fails()) {
            // kembalikan response gagal
            return response()->json([
                'success' => false,
                'message' => 'Gagal register',
                'data' => $validator->errors()
            ], 400);
        }

        // enkripsi password
        $input = $request->all();
        $input['password'] = bcrypt($input['password']);

        // buat user baru
        $user = User::create($input);

        // buat token
        $success['token'] = $user->createToken('auth_token')->plainTextToken;
        $success['name'] = $user->name;

        // kembalikan response
        return response()->json([
            'success' => true,
            'message' => 'Registrasi sukses',
            'data' => $success
        ], 201);
    }

    public function login(Request $request)
    {
        // mencocokkan email dan password
        $credentials = [
            'email' => $request->email,
            'password' => $request->password
        ];

        // jika ada user yang cocok
        if (Auth::attempt($credentials)) {
            // buat token baru
            $auth = Auth::user();
            $success['token'] = $auth->createToken('auth_token')->plainTextToken;
            $success['name'] = $auth->name;
            $success['email'] = $auth->email;


            // kembalikan response
            return response()->json([
                'success' => true,
                'message' => 'Login sukses',
                'data' => $success,
                'user' => auth()->user()
            ], 200);
        } else {
            // jika tidak ada user kembalikan response
            return response()->json([
                'success' => false,
                'message' => 'Cek email dan password lagi',
                'data' => null
            ], 400);
        }
    }


    public function logout(Request $request)
    {
        // hapus token
        auth()->user()->tokens()->delete();

        // kembalikan response
        return response()->json([
            'success' => true,
            'message' => 'Berhasil logout'
        ]);
    }
}
