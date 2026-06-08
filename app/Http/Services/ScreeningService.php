<?php

namespace App\Http\Services;

use App\Models\ScreeningResponses;
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

    public function getResponseMatrix(int $screeningId, $request)
    {
        $screening = Screenings::with('questions')
            ->findOrFail($screeningId);

        $responseQuery = ScreeningResponses::with('details.question')
            ->where('screening_id', $screeningId);

        // Filter tahun
        if ($year = $request->query('year')) {
            $responseQuery->whereYear('created_at', $year);
        }

        $perPage = $request->query('per_page', 10);

        $responses = $responseQuery->paginate($perPage);

        $questions = $screening->questions;

        $rows = collect($responses->items())->map(function ($response) use ($questions) {

            $answers = $response->details->keyBy('question_screening_id');

            $row = [
                'name' => $response->name,
                'email' => $response->email,
                'institution' => $response->institution,
                'major' => $response->major,
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

            // metadata pagination
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
