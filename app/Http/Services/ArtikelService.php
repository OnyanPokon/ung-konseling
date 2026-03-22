<?php

namespace App\Http\Services;

use App\Exceptions\ServiceException;
use App\Http\Traits\FileUpload;
use App\Models\Artikels;
use Exception;
use Illuminate\Support\Facades\DB;

class ArtikelService
{

    use FileUpload;

    protected $path = 'artikel';
    protected $model;

    public function __construct(Artikels $model)
    {
        $this->model = $model;
    }

    public function landing($request)
    {
        $per_page = $request->per_page ?? 10;
        $data = $this->model->where('status', 'publikasi')->latest();

        if ($search = $request->query('search')) {
            $data->where('judul', 'like', '%' . $search . '%');
        }

        if ($status = $request->query('status')) {
            $data->where('status', $status);
        }

        return $data->paginate($per_page);
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

    public function detail($slug)
    {
        $data = $this->model->where('slug', $slug)->where('status', 'publikasi')->first();

        if (!$data) {
            throw new ServiceException('Artikel tidak ditemukan', 404);
        }

        return $data;
    }

    public function store($request)
    {
        DB::beginTransaction();

        try {
            $validatedData = $request->validated();
            $validatedData['slug'] = str()->slug($validatedData['judul']);

            if ($request->hasFile('thumbnail')) {
                $thumbnail = $this->uploadPhotoAndConvertToWebp($request->file('thumbnail'), $this->path);
                $validatedData['thumbnail'] = $thumbnail;
            }

            $data = $this->model->create($validatedData);

            DB::commit();

            return $data;
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
        try {
            $validatedData = $request->validated();
            $data = $this->show($id);
            $validatedData['slug'] = str()->slug($validatedData['judul']);
            if ($request->hasFile('thumbnail')) {
                $thumbnail = $this->uploadPhotoAndConvertToWebp($request->file('thumbnail'), $this->path);
                $validatedData['thumbnail'] = $thumbnail;
                if ($data->thumbnail != 'default.png') {
                    $this->unlinkPhoto($data->thumbnail);
                }
            }
            $data->update([
                'judul' => $validatedData['judul'],
                'slug' => $validatedData['slug'],
                'konten' => $validatedData['konten'],
                'thumnbail' => $validatedData['thumbnail'] ?? $data->thumnbail,
                'status' => $request->status ? $validatedData['status'] : $data->status
            ]);

            return $data;
        } catch (Exception $e) {
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
}
