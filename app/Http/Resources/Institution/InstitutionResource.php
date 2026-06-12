<?php

namespace App\Http\Resources\Institution;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Crypt;

class InstitutionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'           => Crypt::encryptString($this->id),
            'name'         => $this->name,
            'geocode'      => $this->geocode,
            'municipality' => $this->municipality,
            'typeinst'     => $this->typeinst,
            'lat'          => $this->lat,
            'lon'          => $this->lon,
            'deleted_at'   => $this->deleted_at,
            'created_at'   => $this->created_at,
            'updated_at'   => $this->updated_at,
        ];
    }
}