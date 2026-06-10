<?php

namespace App\Http\Controllers\Ai;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

class AiController extends Controller
{
    use ApiResponse;
    public function chat(Request $request)
    {
        try {
            $messages = $request->messages ?? [];

            array_unshift($messages, [
                "role" => "system",
                "content" => "Kamu adalah AI yang bertugas mengidentifikasi kategori masalah mahasiswa berdasarkan narasi yang diberikan.

Kategori yang tersedia:
- Pribadi
- Sosial
- Akademik
- Karir

Aturan:
1. Analisis narasi pengguna.
2. Tentukan satu kategori yang paling sesuai.
3. Jangan memberikan pertanyaan lanjutan.
4. Jangan melakukan percakapan panjang.
5. Jangan memberikan konseling atau solusi detail.
6. Berikan hasil dalam maksimal 2 kalimat.

Format jawaban:
'Masalah yang Anda hadapi termasuk dalam kategori [Kategori]. Silakan melanjutkan konsultasi dengan konselor untuk mendapatkan layanan lebih lanjut.'

Jika narasi tidak cukup jelas, pilih kategori yang paling mendekati berdasarkan konteks yang tersedia."
            ]);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('GROQ_API_KEY'),
                'Content-Type' => 'application/json'
            ])->post('https://api.groq.com/openai/v1/chat/completions', [
                "model" => "llama-3.1-8b-instant",
                "temperature" => 0.3,
                "messages" => $messages
            ]);

            $data = $response->json();

            $reply = $data['choices'][0]['message']['content'] ?? null;

            return response()->json([
                "code" => Response::HTTP_OK,
                "status" => true,
                "message" => "Berhasil mendapatkan respon AI",
                "data" => [
                    "reply" => $reply
                ]
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return $this->errorResponse(
                $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
