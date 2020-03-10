<?php

namespace App\Http\Controllers\Admin\CL;

use App\Http\Requests\CL\QuestionRequest;
use App\Models\CL\Test;
use App\Models\CL\TestQuestion;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class QuestionController extends Controller
{
    public function index(Test $test)
    {
        $questions = $test->questions();

        $questions = $questions->paginate(20);

        return view('admin.cl.test.questions.index', [
            'questions' => $questions,
            'test' => $test,
        ]);
    }

    public function edit(TestQuestion $question)
    {
        $test = $question->test;

        return view('admin.cl.test.questions.edit', [
            'test' => $test,
            'question' => $question,
        ]);
    }

    public function update(QuestionRequest $request, TestQuestion $question)
    {
        $data = $request->mutatorData();

        $question->fill($data);
        $question->save();

        return redirect(route('admin.cl.questions.edit', [
            'question' => $question,
        ]));
    }

    public function create(Test $test)
    {
        return view('admin.cl.test.questions.create', [
            'test' => $test,
        ]);
    }

    public function store(QuestionRequest $request)
    {
        $data = $request->mutatorData();

        $question = new TestQuestion($data);
        $question->save();

        return redirect(route('admin.cl.questions.edit', [
            'question' => $question,
        ]));
    }

    public function destroy(TestQuestion $question)
    {
        try {
            $question->delete();
            $question->answers->delete();
        } catch (\Exception $e) {
            \Log::info($e);
        }

        return redirect()->back();
    }
}
