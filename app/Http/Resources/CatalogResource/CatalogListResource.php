<?php

namespace App\Http\Resources\CatalogResource;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Crypt;

class CatalogListResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'   => Crypt::encryptString($this->id),
            'name' => $this->name,

            'module' => [
                'id'         => $this->module?->id,
                'name'       => $this->module?->name ?? 'Unidad deshabilitada',
                'deleted_at' => $this->module?->deleted_at,
            ],

            'deleted_at' => $this->deleted_at,
        ];
    }
}