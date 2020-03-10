<?php

namespace App\Http\Resources\API\CL\Moderation;

use Illuminate\Http\Resources\Json\JsonResource;

class AnswerResource extends JsonResource
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
            'questionID' => $this->question_id,
            'ruName' => $this->ru_name,
            'kkName' => $this->kk_name,
            'enName' => $this->en_name,
            'isCorrect' => $this->is_correct,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
            'deletedAt' => $this->deleted_at,
        ] : [];
    }
}
