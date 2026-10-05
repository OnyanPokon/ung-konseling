<?php

namespace App\Http\Controllers\Konselis;

use App\Http\Controllers\Controller;
use App\Http\Requests\KonselisRequest;
use App\Http\Resources\KonselisResources;
use App\Http\Services\KonselisService;
use App\Http\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class KonselisController extends Controller
{
    use ApiResponse;

    protected $konselisService;

    public function __construct(KonselisService $konselisService)
    {
        $this->konselisService = $konselisService;
    }

    public function index(Request $request)
    {
        try {
            $data = $this->konselisService->getAll($request);

            return $this->successResponseWithDataIndex(
                $data,
                KonselisResources::collection($data),
                'Data konselis berhasil diambil',
                Response::HTTP_OK
            );
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function getByUserId($userId)
    {
        try {
            $data = $this->konselisService->getByUserId($userId);

            return $this->successResponseWithData(
                KonselisResources::make($data),
                'Data konseli berhasil diambil',
                Response::HTTP_OK
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                $e->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    public function store(KonselisRequest $request)
    {
        try {
            $this->konselisService->store($request);

            return $this->successResponse(
                'Berhasil menambah data konselis',
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

    public function register(KonselisRequest $request)
    {
        try {
            $data = $this->konselisService->register($request);

            return $this->successResponseWithData(
                $data,
                'Registrasi berhasil',
                Response::HTTP_CREATED
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                $e->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    public function show($id)
    {
        try {
            $data = $this->konselisService->show($id);

            return $this->successResponseWithData(
                KonselisResources::make($data),
                'Data konselis berhasil diambil',
                Response::HTTP_OK
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                $e->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    public function update(KonselisRequest $request, $id)
    {
        try {
            $this->konselisService->update($request, $id);

            return $this->successResponse(
                'Berhasil mengubah data konselis',
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
            $this->konselisService->destroy($id);

            return $this->successResponse(
                'Berhasil menghapus data konselis',
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
            $this->konselisService->multiDestroy($request->ids);

            return $this->successResponse(
                'Berhasil menghapus data konselis',
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
