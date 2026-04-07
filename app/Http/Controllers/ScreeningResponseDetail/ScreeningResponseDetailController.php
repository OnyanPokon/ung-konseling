<?php

namespace App\Http\Controllers\ScreeningResponseDetail;

use App\Http\Controllers\Controller;
use App\Http\Requests\ScreeningResponseDetailRequest;
use App\Http\Resources\ScreeningResponseDetailResources;
use App\Http\Services\ScreeningResponseDetailService;
use App\Http\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class ScreeningResponseDetailController extends Controller
{
    use ApiResponse;

    protected $screeningResponseDetailService;

    public function __construct(ScreeningResponseDetailService $screeningResponseDetailService)
    {
        $this->screeningResponseDetailService = $screeningResponseDetailService;
    }

    public function index(Request $request)
    {
        try {
            $data = $this->screeningResponseDetailService->getAll($request);

            return $this->successResponseWithDataIndex(
                $data,
                ScreeningResponseDetailResources::collection($data),
                'Data detail respon berhasil diambil',
                SymfonyResponse::HTTP_OK
            );
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], SymfonyResponse::HTTP_BAD_REQUEST);
        }
    }

    public function store(ScreeningResponseDetailRequest $request)
    {
        try {
            $this->screeningResponseDetailService->store($request);

            return $this->successResponse(
                'Berhasil menambah data detail respon',
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
            $data = $this->screeningResponseDetailService->show($id);

            return $this->successResponseWithData(
                ScreeningResponseDetailResources::make($data),
                'Data detail respon berhasil diambil',
                SymfonyResponse::HTTP_OK
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                $e->getMessage(),
                SymfonyResponse::HTTP_BAD_REQUEST
            );
        }
    }

    public function update(ScreeningResponseDetailRequest $request, $id)
    {
        try {
            $this->screeningResponseDetailService->update($request, $id);

            return $this->successResponse(
                'Berhasil mengubah data detail respon',
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
            $this->screeningResponseDetailService->destroy($id);

            return $this->successResponse(
                'Berhasil menghapus data detail respon',
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
            $this->screeningResponseDetailService->multiDestroy($request->ids);

            return $this->successResponse(
                'Berhasil menghapus data detail respon',
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
