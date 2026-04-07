<?php

namespace App\Http\Services;

use App\Models\QuestionScreenings;
use App\Models\ScreeningResponseDetails;
use App\Models\ScreeningResponses;
use Exception;
use Illuminate\Support\Facades\DB;

class ScreeningResponseService
{
    protected $model;

    public function __construct(ScreeningResponses $model)
    {
        $this->model = $model;
    }

    public function getAll($request)
    {
        $per_page = $request->per_page ?? 10;
        $data = $this->model->with('screening')->orderBy('created_at', 'desc');

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
            $response = ScreeningResponses::create([
                'screening_id' => $data['screening_id'],
                'email' => $data['email'],
                'name' => $data['name'],
                'nim' => $data['nim'],
                'institution' => $data['institution'],
                'major' => $data['major'],
            ]);

            foreach ($data['answers'] as $answer) {
                $question = QuestionScreenings::findOrFail($answer['question_id']);

                if ($answer['score'] > $question->scale) {
                    throw new Exception("Score melebihi skala pada question ID {$question->id}");
                }

                ScreeningResponseDetails::create([
                    'screening_response_id' => $response->id,
                    'question_screening_id' => $question->id, // ✅ FIX
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
        return $this->model->with('screening')->findOrFail($id);
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
