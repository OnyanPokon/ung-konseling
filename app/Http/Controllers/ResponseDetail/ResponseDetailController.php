<?php

namespace App\Http\Controllers\ResponseDetail;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResponseDetailRequest;
use App\Http\Resources\ResponseDetailResources;
use App\Http\Services\ResponseDetailService;
use App\Http\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class ResponseDetailController extends Controller
{
    use ApiResponse;

    protected $responseDetailService;

    public function __construct(ResponseDetailService $responseDetailService)
    {
        $this->responseDetailService = $responseDetailService;
    }

    public function index(Request $request)
    {
        try {
            $data = $this->responseDetailService->getAll($request);

            return $this->successResponseWithDataIndex(
                $data,
                ResponseDetailResources::collection($data),
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

    public function store(ResponseDetailRequest $request)
    {
        try {
            $this->responseDetailService->store($request);

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
            $data = $this->responseDetailService->show($id);

            return $this->successResponseWithData(
                ResponseDetailResources::make($data),
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

    public function update(ResponseDetailRequest $request, $id)
    {
        try {
            $this->responseDetailService->update($request, $id);

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
            $this->responseDetailService->destroy($id);

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
            $this->responseDetailService->multiDestroy($request->ids);

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
