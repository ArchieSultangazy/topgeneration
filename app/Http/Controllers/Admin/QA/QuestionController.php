<?php

namespace App\Http\Controllers\Admin\QA;

use App\Models\QA\Question;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class QuestionController extends Controller
{
    public function index(Request $request)
    {
        $questions = Question::query();
        $keyword = $request->get('search');

        if (!is_null($keyword)) {
            $questions = $questions->where('title', 'LIKE', "%$keyword%");
        }

        $questions = $questions->paginate(20);

        return view('admin.qa.question.index', [
            'questions' => $questions,
        ]);
    }

    public function destroy(Question $question)
    {
        $question->delete();

        return redirect()->route('admin.qa.question.index');
    }
}
