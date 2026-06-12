<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Crypt;


class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => Crypt::encryptString($this->id),
            'name' => $this->name,
            'lastname' => $this->lastname,
            'phone' => $this->phone,
            'mobile' => $this->mobile,
            'address' => $this->address,
            'email' => $this->email,
            'verified_at' => $this->email_verified_at ? true : false,
            
            /* aqui estamos trayendo el rol y la ocupación del usuario */
            'role' => $this->whenLoaded('role', fn() => [
                'id'   => Crypt::encryptString($this->role->id),
                'name' => $this->role->name,
            ]),

            'occupation' => $this->whenLoaded('occupation', fn() => [
                'id'   => Crypt::encryptString($this->occupation->id),
                'name' => $this->occupation->name,
            ]),

            'dependency' => $this->whenLoaded('dependency', fn() => [
                'id'   => Crypt::encryptString($this->dependency->id),
                'name' => $this->dependency->name,
            ]),

            'institution' => $this->whenLoaded('institution', fn() => [
                'id'   => Crypt::encryptString($this->institution->id),
                'name' => $this->institution->name,
            ]),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->when($this->deleted_at, $this->deleted_at),
        ];
    }
}
