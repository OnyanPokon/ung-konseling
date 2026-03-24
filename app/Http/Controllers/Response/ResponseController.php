<?php

namespace App\Http\Controllers\Response;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResponseRequest;
use App\Http\Resources\ResponseResources;
use App\Http\Services\ResponseService;
use App\Http\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class ResponseController extends Controller
{
    use ApiResponse;

    protected $responseService;

    public function __construct(ResponseService $responseService)
    {
        $this->responseService = $responseService;
    }

    public function index(Request $request)
    {
        try {
            $data = $this->responseService->getAll($request);

            return $this->successResponseWithDataIndex(
                $data,
                ResponseResources::collection($data),
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

    public function store(ResponseRequest $request)
    {
        try {
            $this->responseService->store($request->validated());

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
            $data = $this->responseService->show($id);

            return $this->successResponseWithData(
                ResponseResources::make($data),
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

    public function update(ResponseRequest $request, $id)
    {
        try {
            $this->responseService->update($request, $id);

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
            $this->responseService->destroy($id);

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
            $this->responseService->multiDestroy($request->ids);

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
