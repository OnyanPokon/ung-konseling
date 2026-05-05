<?php

namespace App\Http\Services;

use App\Models\Konselis;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class KonselisService
{

    protected $model;

    public function __construct(Konselis $model)
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

            $user = User::create([
                'name' => $data['nama'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            $user->assignRole('konseli');

            Konselis::create([
                'user_id' => $user->id,
                'nim' => $data['nim'],
                'phone' => $data['phone'],
                'domisili' => $data['domisili'],
                'jurusan' => $data['jurusan'],
                'umur' => $data['umur'],
                'jenis_kelamin' => $data['jenis_kelamin'],
            ]);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function register($request)
    {
        return DB::transaction(function () use ($request) {

            $user = User::create([
                'name' => $request->nama,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $user->assignRole('konseli');

            $konseli = Konselis::create([
                'user_id' => $user->id,
                'nim' => $request->nim,
                'phone' => $request->phone,
                'domisili' => $request->domisili,
                'jurusan' => $request->jurusan,
                'umur' => $request->umur,
                'jenis_kelamin' => $request->jenis_kelamin,
            ]);

            return [
                'user_id' => $user->id,
                'konseli_id' => $konseli->id,
            ];
        });
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

            $data = $this->model->findOrFail($id)->update($validatedData);

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
            $konseli = $this->model->with('user')->findOrFail($id);

            // hapus user terkait
            if ($konseli->user) {
                $konseli->user->delete();
            }

            // hapus konseli
            $konseli->delete();

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
}
