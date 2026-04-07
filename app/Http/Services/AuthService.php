<?php

namespace App\Http\Services;

use App\Models\Konselis;
use App\Models\Konselors;
use App\Models\SesiKonselings;
use App\Models\Tikets;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthService
{

    protected $model;
    protected $layananMandiriService;

    public function login($request)
    {
        DB::beginTransaction();
        try {
            $validate = $request->validate([
                'email' => 'required',
                'password' => 'required'
            ]);

            $credentials = filter_var($request->email, FILTER_VALIDATE_EMAIL)
                ? ['email' => $request->email, 'password' => $request->password]
                : ['name' => $request->email, 'password' => $request->password];

            if (!Auth::attempt($credentials)) {
                throw new Exception('Email atau password salah');
            }

            $user = User::where('name', $request->email)->orWhere('email', $request->email)->firstOrFail();

            $token = $user->createToken('auth_token')->plainTextToken;
            $data = [
                'token_type' => 'Bearer',
                'token' => $token,
            ];

            DB::commit();
            return $data;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getUser($request)
    {
        try {

            if ($request->user()->roles->first()->name == 'konseli' || $request->user()->roles->first()->name == 'admin' || $request->user()->roles->first()->name == 'konselor') {
                $responseData = [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'role' => [
                        'id' => $request->user()->roles->first()->id,
                        'name' => $request->user()->roles->first()->name,
                        'permissions' => $request->user()->roles->first()->permissions->pluck('name')
                    ],
                    'permissions' => $request->user()->permissions->pluck('name') ?? '-'
                ];
            }

            return $responseData;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function logout($request)
    {
        $data = $request->user()->tokens()->delete();

        return $data;
    }

    public function updateProfile($request)
    {
        DB::beginTransaction();
        try {
            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'email' => 'sometimes|required|email|unique:users,email,' . $request->user()->id,
            ]);

            $user = $request->user();

            $user->update([
                'name' => $validated['name'],
                'email' => $validated['email']
            ]);

            DB::commit();

            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ];
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function changePassword($request)
    {
        DB::beginTransaction();
        try {
            $validated = $request->validate([
                'current_password' => 'required',
                'new_password' => 'required|min:6|confirmed',
            ]);

            $user = $request->user();

            // cek password lama
            if (!Hash::check($validated['current_password'], $user->password)) {
                throw new Exception('Password lama tidak sesuai');
            }

            $user->update([
                'password' => Hash::make($validated['new_password'])
            ]);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getAdminOverview()
    {
        $user = Auth::user();

        // pastikan hanya admin
        if (!$user->roles->pluck('name')->contains('admin')) {
            throw new Exception('Akses ditolak');
        }

        $totalKonselor = Konselors::count();

        $totalKonseli = Konselis::count();

        $totalTiket = Tikets::count();

        $totalSesiKonseling = SesiKonselings::count();

        return [
            'total_konselor' => $totalKonselor,
            'total_konseli' => $totalKonseli,
            'total_tiket' => $totalTiket,
            'total_sesi_konseling' => $totalSesiKonseling,
        ];
    }
}
