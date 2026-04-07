<?php

namespace App\Http\Controllers\QuestionScreening;

use App\Http\Controllers\Controller;
use App\Http\Requests\QuestionScreeningRequest;
use App\Http\Resources\QuestionScreeningResources;
use App\Http\Services\QuestionScreeningService;
use App\Http\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class QuestionScreeningController extends Controller
{
    use ApiResponse;

    protected $questionScreeningService;

    public function __construct(QuestionScreeningService $questionScreeningService)
    {
        $this->questionScreeningService = $questionScreeningService;
    }

    public function index(Request $request)
    {
        try {
            $data = $this->questionScreeningService->getAll($request);

            return $this->successResponseWithDataIndex(
                $data,
                QuestionScreeningResources::collection($data),
                'Data pertanyaan berhasil diambil',
                Response::HTTP_OK
            );
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function store(QuestionScreeningRequest $request)
    {
        try {
            $this->questionScreeningService->store($request);

            return $this->successResponse(
                'Berhasil menambah data pertanyaan',
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
            $data = $this->questionScreeningService->show($id);

            return $this->successResponseWithData(
                QuestionScreeningResources::make($data),
                'Data pertanyaan berhasil diambil',
                Response::HTTP_OK
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                $e->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    public function update(QuestionScreeningRequest $request, $id)
    {
        try {
            $this->questionScreeningService->update($request, $id);

            return $this->successResponse(
                'Berhasil mengubah data pertanyaan',
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
            $this->questionScreeningService->destroy($id);

            return $this->successResponse(
                'Berhasil menghapus data pertanyaan',
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
            $this->questionScreeningService->multiDestroy($request->ids);

            return $this->successResponse(
                'Berhasil menghapus data pertanyaan',
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
