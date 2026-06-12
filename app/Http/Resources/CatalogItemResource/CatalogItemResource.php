<?php

namespace App\Http\Resources\CatalogItemResource;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Crypt;

class CatalogItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'    => Crypt::encryptString($this->id),
            'value' => $this->value,
            'label' => $this->label,

            'catalog_name' => $this->whenLoaded('catalog', fn () => $this->catalog->name ?? null),

            'catalog_eid' => $this->whenLoaded('catalog', function () {
                if (!$this->catalog?->id) return null;
                return Crypt::encryptString((string) $this->catalog->id);
            }),

            'deleted_at' => $this->when($this->deleted_at, $this->deleted_at),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
