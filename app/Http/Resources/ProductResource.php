<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an API response.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'title' => $this->title,

            'description' => $this->description,

            'category' => $this->whenLoaded('category', function () {
                return [
                    'id' => $this->category->id,
                    'name' => $this->category->name,
                    'slug' => $this->category->slug,
                    'is_active' => (bool) $this->category->is_active,
                ];
            }),

            'vendor' => $this->whenLoaded('dokan', function () {
                return [
                    'id' => $this->dokan->id,
                    'name' => $this->dokan->name,
                    'company_name' => $this->dokan->company_name,
                    'logo' => $this->dokan->logo,
                    'business_location' => $this->dokan->business_location,
                ];
            }),

            'variants' => $this->whenLoaded('varients', function () {
                return $this->varients->map(function ($variant) {
                    return [
                        'id' => $variant->id,
                        'title' => $variant->title,
                        'price' => $variant->price,
                        'discount' => $variant->discount,
                        'qty' => $variant->qty,
                        'images' => $variant->images,
                    ];
                })->values();
            }),

            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}