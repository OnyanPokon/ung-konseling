<?php

namespace App\Http\Controllers\ScreeningResponse;

use App\Http\Controllers\Controller;
use App\Http\Requests\ScreeningResponseRequest;
use App\Http\Resources\ScreeningResponseResources;
use App\Http\Services\ScreeningResponseService;
use App\Http\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class ScreeningResponseController extends Controller
{
    use ApiResponse;

    protected $screeningResponseService;

    public function __construct(ScreeningResponseService $screeningResponseService)
    {
        $this->screeningResponseService = $screeningResponseService;
    }

    public function index(Request $request)
    {
        try {
            $data = $this->screeningResponseService->getAll($request);

            return $this->successResponseWithDataIndex(
                $data,
                ScreeningResponseResources::collection($data),
                'Data respon berhasil diambil',
                SymfonyResponse::HTTP_OK
            );
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], SymfonyResponse::HTTP_BAD_REQUEST);
        }
    }

    public function store(ScreeningResponseRequest $request)
    {
        try {
            $this->screeningResponseService->store($request->validated());

            return $this->successResponse(
                'Berhasil menambah data respon',
                SymfonyResponse::HTTP_CREATED
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                $e->getMessage(),
                SymfonyResponse::HTTP_BAD_REQUEST
            );
        } catch (ValidationException $e) {
            return $this->errorResponse(
                $e->errors(),
                SymfonyResponse::HTTP_BAD_REQUEST
            );
        }
    }

    public function show($id)
    {
        try {
            $data = $this->screeningResponseService->show($id);

            return $this->successResponseWithData(
                ScreeningResponseResources::make($data),
                'Data respon berhasil diambil',
                SymfonyResponse::HTTP_OK
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                $e->getMessage(),
                SymfonyResponse::HTTP_BAD_REQUEST
            );
        }
    }

    public function update(ScreeningResponseRequest $request, $id)
    {
        try {
            $this->screeningResponseService->update($request, $id);

            return $this->successResponse(
                'Berhasil mengubah data respon',
                SymfonyResponse::HTTP_OK
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                $e->getMessage(),
                SymfonyResponse::HTTP_BAD_REQUEST
            );
        } catch (ValidationException $e) {
            return $this->errorResponse(
                $e->errors(),
                SymfonyResponse::HTTP_BAD_REQUEST
            );
        }
    }

    public function destroy($id)
    {
        try {
            $this->screeningResponseService->destroy($id);

            return $this->successResponse(
                'Berhasil menghapus data respon',
                SymfonyResponse::HTTP_OK
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                $e->getMessage(),
                SymfonyResponse::HTTP_BAD_REQUEST
            );
        }
    }

    public function multiDestroy(Request $request)
    {
        try {
            $this->screeningResponseService->multiDestroy($request->ids);

            return $this->successResponse(
                'Berhasil menghapus data respon',
                SymfonyResponse::HTTP_OK
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                $e->getMessage(),
                SymfonyResponse::HTTP_BAD_REQUEST
            );
        }
    }
}
