<?php

namespace App\Http\Controllers\Assessment;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssessmentRequest;
use App\Http\Resources\AssessmentResources;
use App\Http\Services\AssessmentService;
use App\Http\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class AssessmentController extends Controller
{
    use ApiResponse;

    protected $assessmentService;

    public function __construct(AssessmentService $assessmentService)
    {
        $this->assessmentService = $assessmentService;
    }

    public function index(Request $request)
    {
        try {
            $data = $this->assessmentService->getAll($request);

            return $this->successResponseWithDataIndex(
                $data,
                AssessmentResources::collection($data),
                'Data assessment berhasil diambil',
                Response::HTTP_OK
            );
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function store(AssessmentRequest $request)
    {
        try {
            $this->assessmentService->store($request);

            return $this->successResponse(
                'Berhasil menambah data assessment',
                Response::HTTP_CREATED
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                $e->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        } catch (ValidationException $e) {
            return $this->errorResponse(
                $e->errors(),
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    public function show($id)
    {
        try {
            $data = $this->assessmentService->show($id);

            return $this->successResponseWithData(
                AssessmentResources::make($data),
                'Data assessment berhasil diambil',
                Response::HTTP_OK
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                $e->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    public function publicShowBySlug($slug)
    {
        try {
            $data = $this->assessmentService->getBySlug($slug);

            $result = [
                'id' => $data->id,
                'title' => $data->title,
                'description' => $data->description,

                'period' => [
                    'id' => $data->period->id,
                    'name' => $data->period->name,
                ],

                'questions' => $data->questions->map(function ($q) {
                    return [
                        'id' => $q->id,
                        'question_text' => $q->question_text,
                    ];
                })
            ];

            return $this->successResponseWithData(
                $result,
                'Assessment berhasil diambil',
                Response::HTTP_OK
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                $e->getMessage(),
                Response::HTTP_NOT_FOUND
            );
        }
    }

    public function responseMatrix($assessmentId)
    {
        try {
            $data = $this->assessmentService->getResponseMatrix($assessmentId);

            return $this->successResponseWithData(
                $data,
                'Berhasil mengambil data matrix',
                Response::HTTP_OK
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                $e->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        }
    }



    public function update(AssessmentRequest $request, $id)
    {
        try {
            $this->assessmentService->update($request, $id);

            return $this->successResponse(
                'Berhasil mengubah data assessment',
                Response::HTTP_OK
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                $e->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        } catch (ValidationException $e) {
            return $this->errorResponse(
                $e->errors(),
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    public function destroy($id)
    {
        try {
            $this->assessmentService->destroy($id);

            return $this->successResponse(
                'Berhasil menghapus data assessment',
                Response::HTTP_OK
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                $e->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    public function multiDestroy(Request $request)
    {
        try {
            $this->assessmentService->multiDestroy($request->ids);

            return $this->successResponse(
                'Berhasil menghapus data assessment',
                Response::HTTP_OK
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                $e->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        }
    }
}
