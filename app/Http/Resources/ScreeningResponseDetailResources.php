<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScreeningResponseDetailResources extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'screening_response_id' => $this->screening_response_id,

            // ✅ FIX RELASI
            'response' => $this->whenLoaded('response', function () {
                return [
                    'id' => $this->response->id,
                    'name' => $this->response->name,
                ];
            }),

            // ✅ FIX FIELD NAME
            'screening_question_id' => $this->screening_question_id,

            'question' => $this->whenLoaded('question', function () {
                return [
                    'id' => $this->question->id,
                    'question_text' => $this->question->question_text,
                ];
            }),

            'score' => $this->score,
            'created_at' => $this->created_at->format('d F Y'),
            'updated_at' => $this->updated_at->format('d F Y'),
        ];
    }
}
