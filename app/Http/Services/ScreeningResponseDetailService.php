<?php

namespace App\Http\Services;

use App\Models\ScreeningResponseDetails;
use Exception;
use Illuminate\Support\Facades\DB;

class ScreeningResponseDetailService
{
    protected $model;

    public function __construct(ScreeningResponseDetails $model)
    {
        $this->model = $model;
    }

    public function getAll($request)
    {
        $per_page = $request->per_page ?? 10;
        $data = $this->model->with(['response', 'question'])->orderBy('created_at', 'desc');

        if ($search = $request->query('search')) {
            $data->where('score', 'like', '%' . $search . '%');
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
            $detail = ScreeningResponseDetails::create($data);
            DB::commit();
            return $detail;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function show($id)
    {
        return $this->model->with(['response', 'question'])->findOrFail($id);
    }

    public function update($request, $id)
    {
        DB::beginTransaction();
        try {
            $validatedData = $request->validated();
            $data = $this->model->findOrFail($id);
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
            $idArray = explode(",", $ids);
            $data = $this->model->whereIn('id', $idArray)->get();

            if ($data->isEmpty()) {
                DB::rollBack();
                throw new Exception('Data tidak ditemukan');
            }
            $this->model->whereIn('id', $idArray)->delete();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
