<?php

namespace App\Http\Controllers\HariLayanan;

use App\Http\Controllers\Controller;
use App\Http\Requests\HariLayananRequest;
use App\Http\Resources\HariLayananResources;
use App\Http\Services\HariLayananService;
use App\Http\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class HariLayananController extends Controller
{
    use ApiResponse;

    protected $hariLayananService;

    public function __construct(HariLayananService $hariLayananService)
    {
        $this->hariLayananService = $hariLayananService;
    }

    public function index(Request $request)
    {
        try {
            $data = $this->hariLayananService->getAll($request);

            return $this->successResponseWithDataIndex(
                $data,
                HariLayananResources::collection($data),
                'Data hari layanan berhasil diambil',
                Response::HTTP_OK
            );
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function hariByKonselor($konselorId)
    {
        try {
            $data = $this->hariLayananService->getByKonselor($konselorId);

            return $this->successResponseWithDataIndex(
                $data,
                HariLayananResources::collection($data),
                'Data hari layanan berdasarkan konselor berhasil diambil',
                Response::HTTP_OK
            );
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function store(HariLayananRequest $request)
    {
        try {
            $this->hariLayananService->store($request);

            return $this->successResponse(
                'Berhasil menambah data hari layanan',
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
            $data = $this->hariLayananService->show($id);

            return $this->successResponseWithData(
                HariLayananResources::make($data),
                'Data hari layanan berhasil diambil',
                Response::HTTP_OK
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                $e->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    public function update(HariLayananRequest $request, $id)
    {
        try {
            $this->hariLayananService->update($request, $id);

            return $this->successResponse(
                'Berhasil mengubah data hari layanan',
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
            $this->hariLayananService->destroy($id);

            return $this->successResponse(
                'Berhasil menghapus data hari layanan',
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
            $this->hariLayananService->multiDestroy($request->ids);

            return $this->successResponse(
                'Berhasil menghapus data hari layanan',
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
