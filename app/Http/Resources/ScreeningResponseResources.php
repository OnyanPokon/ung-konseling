<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScreeningResponseResources extends JsonResource
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
            'screening_id' => $this->screening_id,
            'screening' => $this->whenLoaded('screening', function() {
                return [
                    'id' => $this->screening->id,
                    'title' => $this->screening->title,
                ];
            }),
            'email' => $this->email,
            'name' => $this->name,
            'nim' => $this->nim,
            'institution' => $this->institution,
            'major' => $this->major,
            'created_at' => $this->created_at->format('d F Y'),
            'updated_at' => $this->updated_at->format('d F Y'),
        ];
    }
}
