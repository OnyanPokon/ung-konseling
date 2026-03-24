<?php

namespace App\Http\Services;

use App\Http\Traits\FileUpload;
use App\Models\LaporanKonseling;
use App\Models\SesiKonselings;
use App\Notifications\SesiKonselingCreated;
use Exception;
use Illuminate\Support\Facades\DB;

class SesiKonselingService
{

    use FileUpload;

    protected $model;

    public function __construct(SesiKonselings $model)
    {
        $this->model = $model;
    }

    public function getAll($request)
    {
        $per_page = $request->per_page ?? 10;
        $data = $this->model->orderBy('created_at');

        if ($search = $request->query('search')) {
            $data->where('hari', 'like', '%' . $search . '%');
        }

        if ($request->konselor_id) {
            $data->where('konselor_id', $request->konselor_id);
        }

        if ($request->konseli_id) {
            $data->whereHas('tiket', function ($query) use ($request) {
                $query->where('konseli_id', $request->konseli_id);
            });
        }

        if ($request->page) {
            $data = $data->paginate($per_page);
        } else {
            $data = $data->get();
        }

        return $data;
    }


    public function store($request)
    {
        DB::beginTransaction();

        try {
            $data = $request->validated();

            $sesi = SesiKonselings::create($data);

            $konseliUser = $sesi->tiket->konseli->user;
            $konseliUser->notify(new SesiKonselingCreated($sesi));

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

    public function createDraftLaporan($sesi, $data)
    {
        if ($sesi->laporan && $sesi->laporan->status === 'final') {
            throw new Exception('Laporan sudah final');
        }

        return LaporanKonseling::updateOrCreate(
            ['sesi_konseling_id' => $sesi->id],
            [
                'konselor_id' => $sesi->konselor_id,

                // ✅ generate otomatis
                'nama_kegiatan' => "Kegiatan Layanan {$sesi->tiket->jenis_layanan} {$sesi->tiket->jenis_keluhan}",

                'jenis_layanan' => $data['jenis_layanan'],
                'tujuan_kegiatan' => $data['tujuan_kegiatan'] ?? null,

                // ✅ generate otomatis
                'waktu_tempat' => "{$sesi->tempat}, {$sesi->tanggal_konseling} {$sesi->jam_mulai} - {$sesi->jam_selesai}",

                // ✅ generate otomatis
                'jumlah_peserta' => $sesi->tiket->konseli->user->name,

                'uraian_kegiatan' => $data['uraian_kegiatan'] ?? null,
                'hasil_dampak' => $data['hasil_dampak'] ?? null,
                'rekomendasi' => $data['rekomendasi'] ?? null,
                'html_content' => $data['html_content'] ?? null,
                'status' => 'draft',
            ]
        );
    }

    public function updateLaporan($data, $sesiKonselingId)
    {
        DB::beginTransaction();

        try {
            $sesi = $this->model->findOrFail($sesiKonselingId);

            $laporan = $sesi->laporan;

            if (!$laporan) {
                throw new Exception('Laporan belum dibuat');
            }

            if ($laporan->status === 'final') {
                throw new Exception('Laporan sudah final, tidak bisa diubah');
            }

            $laporan->update([
                'jenis_layanan' => $data['jenis_layanan'],
                'tujuan_kegiatan' => $data['tujuan_kegiatan'] ?? null,
                'uraian_kegiatan' => $data['uraian_kegiatan'] ?? null,
                'hasil_dampak' => $data['hasil_dampak'] ?? null,
                'rekomendasi' => $data['rekomendasi'] ?? null,
                'html_content' => $data['html_content'] ?? null,
            ]);

            DB::commit();

            return $laporan;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function findBySesiKonselingId($id)
    {
        $sesi = $this->model->with('laporan')->find($id);

        if (!$sesi || !$sesi->laporan) {
            throw new Exception('Laporan tidak ditemukan');
        }

        return $sesi->laporan;
    }

    public function uploadAndFinal($request, $laporan)
    {
        DB::beginTransaction();

        try {
            if ($laporan->status === 'final') {
                throw new Exception('Laporan sudah final');
            }

            if (!$request->hasFile('file')) {
                throw new Exception('File PDF wajib diupload');
            }

            $file = $request->file('file');

            // validasi extension
            $extension = ['pdf'];
            $filePath = $this->uploadDocument($file, $extension, 'laporan');

            $laporan->update([
                'file_path' => $filePath,
                'status' => 'final'
            ]);

            DB::commit();

            return $laporan;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
