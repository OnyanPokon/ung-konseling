<?php

namespace App\Http\Controllers\Artikel;

use App\Http\Controllers\Controller;
use App\Http\Requests\ArtikelRequest;
use App\Http\Resources\ArtikelResources;
use App\Http\Services\ArtikelService;
use App\Http\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class ArtikelController extends Controller
{
    use ApiResponse;

    protected $artikelService;

    public function __construct(ArtikelService $artikelService)
    {
        $this->artikelService = $artikelService;
    }

    public function index(Request $request)
    {
        try {
            $data = $this->artikelService->getAll($request);

            return $this->successResponseWithDataIndex(
                $data,
                ArtikelResources::collection($data),
                'Data artikel berhasil diambil',
                Response::HTTP_OK
            );
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function landing(Request $request)
    {
        $data = $this->artikelService->landing($request);

        return $this->successResponseWithDataIndex(
            $data,
            ArtikelResources::collection($data),
            'Data artikel berhasil diambil',
            Response::HTTP_OK
        );
    }

    public function detail($slug)
    {
        $data = $this->artikelService->detail($slug);

        return $this->successResponseWithData(
            ArtikelResources::make($data),
            'Data  artikel berhasil diambil',
            Response::HTTP_OK
        );
    }


    public function store(ArtikelRequest $request)
    {
        try {
            $this->artikelService->store($request);

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
            $data = $this->artikelService->show($id);

            return $this->successResponseWithData(
                ArtikelResources::make($data),
                'Data artikel berhasil diambil',
                Response::HTTP_OK
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                $e->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    public function update(ArtikelRequest $request, $id)
    {
        try {
            $this->artikelService->update($request, $id);

            return $this->successResponse(
                'Berhasil mengubah data artikel',
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
            $this->artikelService->destroy($id);

            return $this->successResponse(
                'Berhasil menghapus data artikel',
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
            $this->artikelService->multiDestroy($request->ids);

            return $this->successResponse(
                'Berhasil menghapus data artikel',
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
