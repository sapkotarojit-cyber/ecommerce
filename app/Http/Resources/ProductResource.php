<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /** Transform the resource into an API response. */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'category' => $this->whenLoaded('category', function () {
                $category = $this->resource->getRelation('category');

                if (! $category) {
                    return null;
                }

                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'is_active' => (bool) $category->is_active,
                ];
            }),
            'vendor' => $this->whenLoaded('dokan', function () {
                $vendor = $this->resource->getRelation('dokan');

                if (! $vendor) {
                    return null;
                }

                return [
                    'id' => $vendor->id,
                    'name' => $vendor->name,
                    'company_name' => $vendor->company_name,
                    'logo' => $vendor->logo,
                    'business_location' => $vendor->business_location,
                ];
            }),
            'variants' => $this->whenLoaded('varients', function () {
                return $this->resource->getRelation('varients')
                    ->map(function ($variant) {
                        return [
                            'id' => $variant->id,
                            'title' => $variant->title,
                            'price' => $variant->price,
                            'discount' => $variant->discount,
                            'qty' => $variant->qty,
                            'images' => $variant->images,
                        ];
                    })
                    ->values();
            }),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}