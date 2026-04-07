<?php

namespace App\Http\Services;

use App\Http\Traits\FileUpload;
use App\Models\Konselors;
use App\Models\SesiKonselings;
use App\Models\Tikets;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class KonselorService
{

    use FileUpload;

    protected $path = 'konselor';

    protected $model;

    public function __construct(Konselors $model)
    {
        $this->model = $model;
    }

    public function getAll($request)
    {
        $per_page = $request->per_page ?? 10;
        $data = $this->model->orderBy('created_at');

        if ($search = $request->query('search')) {
            $data->where('nama', 'like', '%' . $search . '%');
        }

        if ($request->page) {
            $data = $data->paginate($per_page);
        } else {
            $data = $data->get();
        }

        return $data;
    }

    public function getByUserId($userId)
    {
        return $this->model
            ->with('user')
            ->where('user_id', $userId)
            ->firstOrFail();
    }

    public function store($request)
    {
        DB::beginTransaction();

        try {
            $data = $request->validated();

            if ($request->hasFile('foto_profil')) {
                $thumbnail = $this->uploadPhotoAndConvertToWebp(
                    $request->file('foto_profil'),
                    $this->path
                );

                $data['foto_profil'] = $thumbnail;
            }

            $user = User::create([
                'name' => $data['nama'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            $user->assignRole('konselor');

            Konselors::create([
                'user_id' => $user->id,
                'nip' => $data['nip'],
                'phone' => $data['phone'],
                'jenis_kelamin' => $data['jenis_kelamin'],
                'foto_profil' => $data['foto_profil'] ?? null,
                'is_active' => $data['is_active'],
            ]);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function show($id)
    {
        return $this->model->findOrFail($id);
    }

    public function update($request, $id)
    {
        DB::beginTransaction();
        try {
            $validatedData = $request->validated();

            $data = $this->show($id);

            // ✅ update user (nama & email)
            if (isset($validatedData['nama']) || isset($validatedData['email'])) {
                $data->user->update([
                    'name' => $validatedData['nama'] ?? $data->user->name,
                    'email' => $validatedData['email'] ?? $data->user->email,
                ]);
            }

            // upload foto
            if ($request->hasFile('foto_profil')) {
                $foto_profil = $this->uploadPhotoAndConvertToWebp(
                    $request->file('foto_profil'),
                    $this->path
                );

                $validatedData['foto_profil'] = $foto_profil;

                if ($data->foto_profil != 'default.png') {
                    $this->unlinkPhoto($data->foto_profil);
                }
            }

            unset($validatedData['nama'], $validatedData['email']);

            // update konselors
            $data->update($validatedData);

            DB::commit();

            return $data;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $data = $this->model->findOrFail($id);

            $data->delete();

            DB::commit();
        } catch (Exception $e) {

            DB::rollBack();
            throw $e;
        }
    }

    public function multiDestroy($ids)
    {
        DB::beginTransaction();
        try {
            $data = $this->model->whereIn('id', explode(",", $ids))->get();

            if ($data->isEmpty()) {
                DB::rollBack();
                throw new Exception('Data tidak ditemukan');
            }
            $this->model->whereIn('id', explode(",", $ids))->delete();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getOverview()
    {
        $user = Auth::user();
        $konselor = $user->konselor;

        if (!$konselor) {
            return [
                'total_tiket_menunggu_verifikasi' => 0,
                'total_sesi_konseling_aktif' => 0,
                'total_sesi_konseling_hari_ini' => 0,
            ];
        }

        $konselorId = $konselor->id;

        $totalTiketMenunggu = Tikets::where('konselor_id', $konselorId)
            ->where('status', 'menunggu')
            ->count();

        $totalSesiAktif = SesiKonselings::where('konselor_id', $konselorId)
            ->where('status', 'aktif')
            ->count();

        $totalSesiHariIni = SesiKonselings::where('konselor_id', $konselorId)
            ->whereDate('tanggal_konseling', now())
            ->count();

        return [
            'total_tiket_menunggu_verifikasi' => $totalTiketMenunggu,
            'total_sesi_konseling_aktif' => $totalSesiAktif,
            'total_sesi_konseling_hari_ini' => $totalSesiHariIni,
        ];
    }
}
