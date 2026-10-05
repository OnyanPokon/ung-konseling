<?php

namespace App\Http\Controllers\JadwalKonselor;

use App\Http\Controllers\Controller;
use App\Http\Requests\JadwalKonselorRequest;
use App\Http\Resources\JadwalKonselorResources;
use App\Http\Services\JadwalKonselorService;
use App\Http\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class JadwalKonselorController extends Controller
{
    use ApiResponse;

    protected $jadwalKonselorService;

    public function __construct(JadwalKonselorService $jadwalKonselorService)
    {
        $this->jadwalKonselorService = $jadwalKonselorService;
    }

    public function index(Request $request)
    {
        try {
            $data = $this->jadwalKonselorService->getAll($request);

            return $this->successResponseWithDataIndex(
                $data,
                JadwalKonselorResources::collection($data),
                'Data jadwal konselor berhasil diambil',
                Response::HTTP_OK
            );
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function store(JadwalKonselorRequest $request)
    {
        try {
            $this->jadwalKonselorService->store($request);

            return $this->successResponse(
                'Berhasil menambah data jadwal konselor',
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
            $data = $this->jadwalKonselorService->show($id);

            return $this->successResponseWithData(
                JadwalKonselorResources::make($data),
                'Data jadwal konselor berhasil diambil',
                Response::HTTP_OK
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                $e->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    public function update(JadwalKonselorRequest $request, $id)
    {
        try {
            $this->jadwalKonselorService->update($request, $id);

            return $this->successResponse(
                'Berhasil mengubah data jadwal konselor',
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
            $this->jadwalKonselorService->destroy($id);

            return $this->successResponse(
                'Berhasil menghapus data jadwal konselor',
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
            $this->jadwalKonselorService->multiDestroy($request->ids);

            return $this->successResponse(
                'Berhasil menghapus data jadwal konselor',
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
