<?php

namespace App\Http\Controllers\Konselor;

use App\Http\Controllers\Controller;
use App\Http\Requests\KonselorRequest;
use App\Http\Resources\KonselorResources;
use App\Http\Services\KonselorService;
use App\Http\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class KonselorController extends Controller
{
    use ApiResponse;

    protected $konselorService;

    public function __construct(KonselorService $konselorService)
    {
        $this->konselorService = $konselorService;
    }

    public function index(Request $request)
    {
        try {
            $data = $this->konselorService->getAll($request);

            return $this->successResponseWithDataIndex(
                $data,
                KonselorResources::collection($data),
                'Data konselor berhasil diambil',
                Response::HTTP_OK
            );
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function getByUserId($userId)
    {
        try {
            $data = $this->konselorService->getByUserId($userId);

            return $this->successResponseWithData(
                KonselorResources::make($data),
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

    public function store(KonselorRequest $request)
    {
        try {
            $this->konselorService->store($request);

            return $this->successResponse(
                'Berhasil menambah data konselor',
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
            $data = $this->konselorService->show($id);

            return $this->successResponseWithData(
                KonselorResources::make($data),
                'Data konselor berhasil diambil',
                Response::HTTP_OK
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                $e->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    public function update(KonselorRequest $request, $id)
    {
        try {
            $this->konselorService->update($request, $id);

            return $this->successResponse(
                'Berhasil mengubah data konselor',
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
            $this->konselorService->destroy($id);

            return $this->successResponse(
                'Berhasil menghapus data konselor',
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
            $this->konselorService->multiDestroy($request->ids);

            return $this->successResponse(
                'Berhasil menghapus data konselor',
                Response::HTTP_OK
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                $e->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    public function overview()
    {
        try {
            $data = $this->konselorService->getOverview();

            return $this->successResponseWithData(
                $data,
                'Data overview berhasil diambil',
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
