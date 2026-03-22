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
               "content" => "Kamu adalah AI konselor mahasiswa yang hangat, empatik, dan suportif.

                Tugas utama:
                1. Dengarkan cerita mahasiswa.
                2. Tentukan kategori masalah utama: Pribadi, Sosial, Akademik, Karir.
                3. Setelah itu, berikan saran/langkah awal.

                Petunjuk gaya bahasa:
                - Balas seperti kamu sedang ngobrol langsung dengan mahasiswa, santai tapi tetap sopan.
                - Jangan pakai format JSON atau laporan resmi.
                - Masukkan kategori masalah di awal jawaban secara alami, misal: 'Sepertinya masalah ini masuk ke kategori Akademik.'
                - Saran bisa diberikan sebagai beberapa langkah praktis atau tips ringan.
                - Bisa tambahkan pertanyaan reflektif untuk mendorong mahasiswa berpikir lebih jauh."
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
