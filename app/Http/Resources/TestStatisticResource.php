<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TestStatisticResource extends JsonResource
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
            'userID' => $this->user_id,
            'testID' => $this->test_id,
            'lessonID' => $this->lesson_id,
            'questionsCount' => $this->questionsCount,
            'answeredQuestions' => $this->result,
            'answeredQuestionsPercent' => $this->result_percent,
            'try' => $this->try,
            'finishedTime' => $this->finishedTime,
            'user' => [
                'id' => $this->user->id,
                'userName' => $this->user->username,
                'phone' => $this->user->phone,
                'email' => $this->user->email,
                'firstName' => $this->user->firstname,
                'lastName' => $this->user->lastname,
                'middleName' => $this->user->middlename,
                'avatar' => $this->user->avatar,
            ],
            'finishedAt' => $this->finished_at,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
            'deletedAt' => $this->deleted_at,
        ] : [];
    }
}
