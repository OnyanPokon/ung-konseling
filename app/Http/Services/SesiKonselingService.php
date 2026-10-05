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

    /**
     * The fields a konseli is told about. A move is any change to one of them;
     * an edit to anything else is not their business and stays silent.
     */
    private const SCHEDULE_FIELDS = ['tanggal_konseling', 'jam_mulai', 'jam_selesai', 'tempat'];

    /**
     * The status that tells the konseli to stop coming. Entering it is the one
     * status change they are told about; every other transition — including the
     * dijadwalkan_ulang a move is usually recorded with — is silent on its own
     * account, and says nothing on its own about whether the session moved.
     */
    private const CANCELLED_STATUS = 'dibatalkan';

    protected $model;

    protected $whatsAppService;

    public function __construct(SesiKonselings $model, SesiKonselingWhatsAppService $whatsAppService)
    {
        $this->model = $model;
        $this->whatsAppService = $whatsAppService;
    }

    public function getAll($request)
    {
        $per_page = $request->per_page ?? 10;
        $data = $this->model->orderBy('created_at');

        if ($search = $request->query('search')) {
            $data->where('hari', 'like', '%'.$search.'%');
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

        // Deliberately outside the transaction above: the schedule is already
        // committed, so a slow gateway can neither hold a database
        // transaction open nor cost the konseli their appointment.
        $this->whatsAppService->sendApproval($sesi);

        return $sesi;
    }

    public function show($id)
    {
        return $this->model->findOrFail($id);
    }

    public function update($request, $id)
    {
        $moved = false;
        $cancelled = false;

        DB::beginTransaction();
        try {
            $validatedData = $request->validated();

            $sesi = $this->model->findOrFail($id);

            // Read before the write: what the konseli would compare against has
            // to be what was actually stored, not what the caller submitted.
            $scheduleBefore = $sesi->only(self::SCHEDULE_FIELDS);
            $statusBefore = $sesi->status;

            $data = $sesi->update($validatedData);

            // Re-read after it, so a value the database stored differently than
            // it was submitted does not read as a move nobody made.
            $sesiAfter = $sesi->fresh();

            $moved = $scheduleBefore !== $sesiAfter->only(self::SCHEDULE_FIELDS);
            $cancelled = $statusBefore !== self::CANCELLED_STATUS
                && $sesiAfter->status === self::CANCELLED_STATUS;

            // A cancelled session has no live appointment left to move, so
            // tidying up its fields afterwards is not news to the konseli, and
            // telling them it moved would contradict the cancellation they were
            // just sent. This guards the status the session is left in, not the
            // one it came from, so a cancellation in the same request wins too.
            $stillLive = $sesiAfter->status !== self::CANCELLED_STATUS;

            DB::commit();
        } catch (Exception $e) {

            DB::rollBack();
            throw $e;
        }

        // Deliberately outside the transaction above, and only for a change the
        // konseli has to be told about, for the same reason store() notifies
        // after committing: the new schedule is already durable, so a slow or
        // broken gateway can neither hold a transaction open nor stop a
        // counselor fixing a scheduling mistake.
        //
        // A cancellation wins over a move, because the frontend sends the whole
        // record back on every edit: one request can carry both, and telling
        // the konseli their session moved and then that it is off would be two
        // messages about a single change they are not going to act on.
        if ($cancelled) {
            $this->whatsAppService->sendCancellation($sesi);
        } elseif ($moved && $stillLive) {
            $this->whatsAppService->sendReschedule($sesi);
        }

        return $data;
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
            $data = $this->model->whereIn('id', explode(',', $ids))->get();

            if ($data->isEmpty()) {
                DB::rollBack();
                throw new Exception('Data tidak ditemukan');
            }
            $this->model->whereIn('id', explode(',', $ids))->delete();

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

            if (! $laporan) {
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

        if (! $sesi || ! $sesi->laporan) {
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

            if (! $request->hasFile('file')) {
                throw new Exception('File PDF wajib diupload');
            }

            $file = $request->file('file');

            // validasi extension
            $extension = ['pdf'];
            $filePath = $this->uploadDocument($file, $extension, 'laporan');

            $laporan->update([
                'file_path' => $filePath,
                'status' => 'final',
            ]);

            DB::commit();

            return $laporan;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
