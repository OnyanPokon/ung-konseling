<?php

namespace App\Http\Controllers\Period;

use App\Http\Controllers\Controller;
use App\Http\Requests\PeriodRequest;
use App\Http\Resources\PeriodResources;
use App\Http\Services\PeriodService;
use App\Http\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class PeriodController extends Controller
{
    use ApiResponse;

    protected $periodService;

    public function __construct(PeriodService $periodService)
    {
        $this->periodService = $periodService;
    }

    public function index(Request $request)
    {
        try {
            $data = $this->periodService->getAll($request);

            return $this->successResponseWithDataIndex(
                $data,
                PeriodResources::collection($data),
                'Data periode berhasil diambil',
                Response::HTTP_OK
            );
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function store(PeriodRequest $request)
    {
        try {
            $this->periodService->store($request);

            return $this->successResponse(
                'Berhasil menambah data periode',
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
            $data = $this->periodService->show($id);

            return $this->successResponseWithData(
                PeriodResources::make($data),
                'Data periode berhasil diambil',
                Response::HTTP_OK
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                $e->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    public function update(PeriodRequest $request, $id)
    {
        try {
            $this->periodService->update($request, $id);

            return $this->successResponse(
                'Berhasil mengubah data periode',
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
            $this->periodService->destroy($id);

            return $this->successResponse(
                'Berhasil menghapus data periode',
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
            $this->periodService->multiDestroy($request->ids);

            return $this->successResponse(
                'Berhasil menghapus data periode',
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
