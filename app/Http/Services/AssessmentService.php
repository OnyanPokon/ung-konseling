<?php

namespace App\Http\Services;

use App\Models\Assessments;
use App\Models\Responses;
use Exception;
use Illuminate\Support\Facades\DB;

class AssessmentService
{
    protected $model;

    public function __construct(Assessments $model)
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
        return Assessments::with('questions')
            ->where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();
    }

    public function getResponseMatrix(int $assessmentId, $request)
    {
        $assessment = Assessments::with('questions')
            ->findOrFail($assessmentId);

         $responseQuery = Responses::with('details.question')
            ->where('assessment_id', $assessmentId);

        // Filter tahun
        if ($year = $request->query('year')) {
            $responseQuery->whereYear('created_at', $year);
        }

        $perPage = $request->query('per_page', 10);

        $responses = $responseQuery->paginate($perPage);

        $questions = $assessment->questions;

        $rows = collect($responses->items())->map(function ($response) use ($questions) {

            $answers = $response->details->keyBy('question_id');

            $row = [
                'name' => $response->name,
                'age' => $response->age,
                'parent_job' => $response->parent_job,
                'domisili' => $response->domisili,
                'gender' => $response->gender,
                'job' => $response->job,
                'institution' => $response->institution,
                'createdAt' => $response->created_at->toDateTimeString(),

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
            'rows' => $rows,
            'pagination' => [
                'current_page' => $responses->currentPage(),
                'last_page' => $responses->lastPage(),
                'per_page' => $responses->perPage(),
                'total' => $responses->total(),
            ]
        ];
    }

    public function store($request)
    {
        DB::beginTransaction();

        try {
            $data = $request->validated();
            $assessment = Assessments::create($data);
            DB::commit();
            return $assessment;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function show($id)
    {
        $data = $this->model->findOrFail($id);
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
