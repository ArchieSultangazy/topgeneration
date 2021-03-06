<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class QuestionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->resource ? [
            'id' => $this->id,
            'ru_name' => $this->ru_name,
            'kk_name' => $this->kk_name,
            'en_name' => $this->en_name,
            'answers' => AnswerResource::collection($this->answers),
        ] : [];
    }
}
