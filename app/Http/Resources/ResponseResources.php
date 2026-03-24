<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResponseResources extends JsonResource
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
            'assessment_id' => $this->assessment_id,
            'assessment' => $this->whenLoaded('assessment', function() {
                return [
                    'id' => $this->assessment->id,
                    'title' => $this->assessment->title,
                ];
            }),
            'name' => $this->name,
            'email' => $this->email,
            'institution' => $this->institution,
            'created_at' => $this->created_at->format('d F Y'),
            'updated_at' => $this->updated_at->format('d F Y'),
        ];
    }
}
