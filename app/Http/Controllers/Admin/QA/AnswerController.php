<?php

namespace App\Http\Controllers\Admin\QA;

use App\Models\QA\Answer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AnswerController extends Controller
{
    public function index(Request $request)
    {
        $answers = Answer::query();
        $keyword = $request->get('search');

        if (!is_null($keyword)) {
            $answers = $answers->where('body', 'LIKE', "%$keyword%");
        }

        $answers = $answers->paginate(20);

        return view('admin.qa.answer.index', [
            'answers' => $answers,
        ]);
    }

    public function destroy(Answer $answer)
    {
        $answer->delete();

        return redirect()->route('admin.qa.answer.index');
    }
}
