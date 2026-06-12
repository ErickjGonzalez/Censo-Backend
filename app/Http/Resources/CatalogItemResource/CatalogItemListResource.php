<?php

namespace App\Http\Resources\CatalogItemResource;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Crypt;

class CatalogItemListResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'    => Crypt::encryptString($this->id),
            'value' => $this->value,
            'label' => $this->label,

            'catalog' => [
                'id'   => $this->catalog?->id,
                'name' => $this->catalog?->name ?? 'Catálogo deshabilitado',
            ],

            'deleted_at' => $this->deleted_at,
        ];
    }
}
