<?php

namespace App\Http\Controllers\Tiket;

use App\Http\Controllers\Controller;
use App\Http\Requests\TiketRequest;
use App\Http\Resources\TiketResources;
use App\Http\Services\TiketService;
use App\Http\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class TiketController extends Controller
{
    use ApiResponse;

    protected $tiketService;

    public function __construct(TiketService $tiketService)
    {
        $this->tiketService = $tiketService;
    }

    public function index(Request $request)
    {
        try {
            $data = $this->tiketService->getAll($request);

            return $this->successResponseWithDataIndex(
                $data,
                TiketResources::collection($data),
                'Data tiket berhasil diambil',
                Response::HTTP_OK
            );
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function store(TiketRequest $request)
    {
        try {
            $this->tiketService->store($request);

            return $this->successResponse(
                'Berhasil menambah data tiket',
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
            $data = $this->tiketService->show($id);

            return $this->successResponseWithData(
                TiketResources::make($data),
                'Data tiket berhasil diambil',
                Response::HTTP_OK
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                $e->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    public function update(TiketRequest $request, $id)
    {
        try {
            $this->tiketService->update($request, $id);

            return $this->successResponse(
                'Berhasil mengubah data tiket',
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
            $this->tiketService->destroy($id);

            return $this->successResponse(
                'Berhasil menghapus data tiket',
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
            $this->tiketService->multiDestroy($request->ids);

            return $this->successResponse(
                'Berhasil menghapus data tiket',
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
