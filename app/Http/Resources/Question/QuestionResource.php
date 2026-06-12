<?php

namespace App\Http\Resources\Question;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Option\OptionResource;
use Illuminate\Support\Facades\Crypt;

class QuestionResource extends JsonResource
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
            'instructions' => $this->instructions,
            'commentaries' => (bool) $this->commentaries,
            'question_structure' => $this->question_structure,
            'created_at'   => $this->created_at,
            'updated_at'   => $this->updated_at,
            'deleted_at'   => $this->when($this->deleted_at, $this->deleted_at),
        ];

    }
}
