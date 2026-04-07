<?php

namespace App\Http\Services;

use App\Models\Questions;
use App\Models\ResponseDetails;
use App\Models\Responses;
use Exception;
use Illuminate\Support\Facades\DB;

class ResponseService
{
    protected $model;

    public function __construct(Responses $model)
    {
        $this->model = $model;
    }

    public function getAll($request)
    {
        $per_page = $request->per_page ?? 10;
        $data = $this->model->with('assessment')->orderBy('created_at', 'desc');

        if ($search = $request->query('search')) {
            $data->where('name', 'like', '%' . $search . '%');
        }

        if ($request->page) {
            $data = $data->paginate($per_page);
        } else {
            $data = $data->get();
        }

        return $data;
    }

    public function store(array $data)
    {
        DB::beginTransaction();

        try {
            $response = Responses::create([
                'assessment_id' => $data['assessment_id'],
                'name' => $data['name'],
                'age' => $data['age'],
                'parent_job' => $data['parent_job'],
                'domisili' => $data['domisili'],
                'gender' => $data['gender'],
                'job' => $data['job'],
                'institution' => $data['institution'],
            ]);

            foreach ($data['answers'] as $answer) {
                $question = Questions::findOrFail($answer['question_id']);

                if ($answer['score'] > $question->scale) {
                    throw new Exception("Score melebihi skala pada question ID {$question->id}");
                }

                ResponseDetails::create([
                    'response_id' => $response->id,
                    'question_id' => $question->id,
                    'score' => $answer['score'],
                ]);
            }

            DB::commit();
            return $response;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function show($id)
    {
        return $this->model->with('assessment')->findOrFail($id);
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
