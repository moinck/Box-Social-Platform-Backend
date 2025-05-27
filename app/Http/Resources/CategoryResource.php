<?php

namespace App\Http\Resources;

use App\Helpers\Helpers;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => Helpers::encrypt($this->id),
            'name' => $this->name,
            'image' => asset($this->image),
            // 'description' => $this->description,
            'sub_categories' => $this->whenLoaded('children', function () {
                return $this->children->map(function ($child) {
                    return [
                        'id' => Helpers::encrypt($child->id),
                        'name' => $child->name,
                    ];
                });
            }),
        ];
    }
}
