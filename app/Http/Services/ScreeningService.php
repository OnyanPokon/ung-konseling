<?php

namespace App\Http\Services;

use App\Models\Screenings;
use Exception;
use Illuminate\Support\Facades\DB;

class ScreeningService
{
    protected $model;

    public function __construct(Screenings $model)
    {
        $this->model = $model;
    }

    public function getAll($request)
    {
        $per_page = $request->per_page ?? 10;
        $data = $this->model->orderBy('created_at', 'desc');

        if ($search = $request->query('search')) {
            $data->where('title', 'like', '%' . $search . '%');
        }

        if ($request->page) {
            $data = $data->paginate($per_page);
        } else {
            $data = $data->get();
        }

        return $data;
    }

    public function getBySlug(string $slug)
    {
        return Screenings::with('questions')
            ->where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();
    }

    public function getResponseMatrix(int $screeningId)
    {
        $screening = Screenings::with([
            'questions',
            'responses.details.question'
        ])->findOrFail($screeningId);

        $questions = $screening->questions;

        $rows = $screening->responses->map(function ($response) use ($questions) {

            $answers = $response->details->keyBy('question_screening_id');

            $row = [
                'name' => $response->name,
                'email' => $response->email,
                'institution' => $response->institution,
            ];

            foreach ($questions as $q) {
                $row['q_' . $q->id] = $answers[$q->id]->score ?? null;
            }

            return $row;
        });

        return [
            'questions' => $questions->map(fn($q) => [
                'id' => $q->id,
                'text' => $q->question_text,
                'scale' => $q->scale,
            ]),
            'rows' => $rows
        ];
    }

    public function store($request)
    {
        DB::beginTransaction();

        try {
            $data = $request->validated();
            $screening = Screenings::create($data);
            DB::commit();
            return $screening;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function show($id)
    {
        return $this->model->with('questions')->findOrFail($id);
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
