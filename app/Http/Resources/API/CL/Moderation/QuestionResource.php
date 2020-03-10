<?php

namespace App\Http\Resources\API\CL\Moderation;

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
            'lessonTestID' => $this->lesson_test_id,
            'ruName' => $this->ru_name,
            'kkName' => $this->kk_name,
            'enName' => $this->en_name,
            'correctAnswerID' => $this->correct_answer_id,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
            'deletedAt' => $this->deleted_at,
        ] : [];
    }
}
