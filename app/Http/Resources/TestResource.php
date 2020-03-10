<?php

namespace App\Http\Resources;

use App\Models\CL\Lesson;
use App\Models\UserResults;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class TestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $questions = $this->questions;
        $lesson = Lesson::find($this->lesson_id);
        if (!is_null($lesson->position)) {
            $nextLesson = Lesson::where('course_id', $lesson->course_id)->where('position', '>', $lesson->position)->orderBy('position','asc')->first()->id ?? null;
        } else {
            $nextLesson = Lesson::where('course_id', $lesson->course_id)->where('id', '>', $lesson->id)->orderBy('id','asc')->first()->id ?? null;
        }
        $previousResult = UserResults::where('user_id', Auth::guard('api')->id())
            ->where('test_id', $this->id)
            ->orderBy('created_at', 'DESC')
            ->first();

        return $this->resource ? [
            'id' => $this->id,
            'course_title' => $lesson->course->title,
            'course_id' => $lesson->course->id,
            'lesson_title' => $lesson->title,
            'lesson_id' => $lesson->id,
            'next_lesson' => $nextLesson,
            'previous_result' => $previousResult,
            'questions' => QuestionResource::collection($questions),
        ] : [];
    }
}
