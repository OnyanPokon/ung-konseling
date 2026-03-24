<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssessmentResources extends JsonResource
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
            'period' =>  [
                'id' => $this->period->id,
                'name' => $this->period->name,
                'start_date' => $this->period->start_date,
                'end_date' => $this->period->end_date,
            ],
            'title' => $this->title,
            'description' => $this->description,
            'is_published' => $this->is_published,
            'slug' => $this->slug,
            'created_at' => $this->created_at->format('d F Y'),
            'updated_at' => $this->updated_at->format('d F Y'),
        ];
    }
}
