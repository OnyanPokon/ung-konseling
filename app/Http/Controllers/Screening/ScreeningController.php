<?php

namespace App\Http\Controllers\Screening;

use App\Http\Controllers\Controller;
use App\Http\Requests\ScreeningRequest;
use App\Http\Resources\ScreeningResources;
use App\Http\Services\ScreeningService;
use App\Http\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class ScreeningController extends Controller
{
    use ApiResponse;

    protected $screeningService;

    public function __construct(ScreeningService $screeningService)
    {
        $this->screeningService = $screeningService;
    }

    public function index(Request $request)
    {
        try {
            $data = $this->screeningService->getAll($request);

            return $this->successResponseWithDataIndex(
                $data,
                ScreeningResources::collection($data),
                'Data Asessment berhasil diambil',
                Response::HTTP_OK
            );
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function store(ScreeningRequest $request)
    {
        try {
            $this->screeningService->store($request);

            return $this->successResponse(
                'Berhasil menambah data Asessment',
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
            $data = $this->screeningService->show($id);

            return $this->successResponseWithData(
                ScreeningResources::make($data),
                'Data Asessment berhasil diambil',
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
            $data = $this->screeningService->getBySlug($slug);

            $result = [
                'id' => $data->id,
                'title' => $data->title,
                'description' => $data->description,
                'questions' => $data->questions->map(function ($q) {
                    return [
                        'id' => $q->id,
                        'question_text' => $q->question_text,
                        'scale' => $q->scale
                    ];
                })
            ];

            return $this->successResponseWithData(
                $result,
                'Screening berhasil diambil',
                Response::HTTP_OK
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                $e->getMessage(),
                Response::HTTP_NOT_FOUND
            );
        }
    }

    public function responseMatrix($ScreeningId)
    {
        try {
            $data = $this->screeningService->getResponseMatrix($ScreeningId);

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



    public function update(ScreeningRequest $request, $id)
    {
        try {
            $this->screeningService->update($request, $id);

            return $this->successResponse(
                'Berhasil mengubah data Asessment',
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
            $this->screeningService->destroy($id);

            return $this->successResponse(
                'Berhasil menghapus data Asessment',
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
            $this->screeningService->multiDestroy($request->ids);

            return $this->successResponse(
                'Berhasil menghapus data Asessment',
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
