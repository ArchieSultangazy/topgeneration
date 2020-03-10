<?php

namespace App\Http\Controllers\Admin\CL;

use App\Http\Requests\CL\AnswerRequest;
use App\Models\CL\TestAnswer;
use App\Models\CL\TestQuestion;
use App\Http\Controllers\Controller;

class AnswerController extends Controller
{
    public function index(TestQuestion $question)
    {
        $answers = $question->answers();

        $answers = $answers->paginate(20);

        return view('admin.cl.test.answers.index', [
            'answers' => $answers,
            'question' => $question,
        ]);
    }

    public function edit(TestAnswer $answer)
    {
        $question = $answer->question;
        
        return view('admin.cl.test.answers.edit', [
            'question' => $question,
            'answer' => $answer,
        ]);
    }

    public function update(AnswerRequest $request, TestAnswer $answer)
    {
        $data = $request->mutatorData();

        $answer->fill($data);
        $answer->save();

        $isCorrect = $request->input('is_correct', null);
        $question = $answer->question;

        if ($isCorrect && $isCorrect == 1) {
            $question->correct_answer_id = $answer->id;
        } else {
            $question->correct_answer_id = 0;
        }

        return redirect(route('admin.cl.answers.edit', [
            'answer' => $answer,
        ]));
    }

    public function store(AnswerRequest $request)
    {
        $data = $request->mutatorData();

        $answer = new TestAnswer($data);
        $answer->save();

        $isCorrect = $request->input('is_correct', null);

        if ($isCorrect && $isCorrect == 1) {
            $question = $answer->question;
            $question->correct_answer_id = $answer->id;
            $question->save();
        }


        return redirect(route('admin.cl.answers.edit', [
            'answer' => $answer,
        ])) ;
    }

    public function create(TestQuestion $question)
    {
        return view('admin.cl.test.answers.create', [
            'question' => $question,
        ]);
    }

    public function destroy(TestAnswer $answer)
    {
        try {
            $answer->delete();
        } catch (\Exception $e) {
            \Log::info($e);
        }

        return redirect()->back();
    }
}
