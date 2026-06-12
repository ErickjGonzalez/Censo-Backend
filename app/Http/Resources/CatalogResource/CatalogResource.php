<?php

namespace App\Http\Resources\CatalogResource;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Crypt;

class CatalogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'   => Crypt::encryptString($this->id),
            'name' => $this->name,
            'slug' => $this->slug,

            'module_name' => $this->whenLoaded('module', fn () => $this->module->name ?? null),
            'module_id'   => $this->whenLoaded('module', function () {
                return $this->module?->id ? Crypt::encryptString((string) $this->module->id) : null;
            }),

           
            'module_is_active' => $this->whenLoaded('module', function () {
                return $this->module ? $this->module->deleted_at === null : null;
            }),

            'deleted_at' => $this->when($this->deleted_at, $this->deleted_at),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}