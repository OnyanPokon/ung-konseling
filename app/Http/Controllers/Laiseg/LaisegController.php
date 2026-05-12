<?php

namespace App\Http\Controllers\Laiseg;

use App\Http\Controllers\Controller;
use App\Http\Requests\LaisegRequest;
use App\Http\Resources\LaisegResources;
use App\Http\Services\LaisegService;
use App\Http\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class LaisegController extends Controller
{
    use ApiResponse;

    protected $laisegService;

    public function __construct(LaisegService $laisegService)
    {
        $this->laisegService = $laisegService;
    }

    public function index(Request $request)
    {
        try {
            $data = $this->laisegService->getAll($request);

            return $this->successResponseWithDataIndex(
                $data,
                LaisegResources::collection($data),
                'Data laiseg berhasil diambil',
                Response::HTTP_OK
            );
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function store(LaisegRequest $request)
    {
        try {
            $this->laisegService->store($request);

            return $this->successResponse(
                'Berhasil menambah data laiseg',
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
            $data = $this->laisegService->show($id);

            return $this->successResponseWithData(
                LaisegResources::make($data),
                'Data laiseg berhasil diambil',
                Response::HTTP_OK
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                $e->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    public function update(LaisegRequest $request, $id)
    {
        try {
            $this->laisegService->update($request, $id);

            return $this->successResponse(
                'Berhasil mengubah data laiseg',
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
            $this->laisegService->destroy($id);

            return $this->successResponse(
                'Berhasil menghapus data laiseg',
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
            $this->laisegService->multiDestroy($request->ids);

            return $this->successResponse(
                'Berhasil menghapus data laiseg',
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
