<?php

namespace App\Http\Controllers\Admin\CL;

use App\Models\CL\LessonComment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class LessonCommentController extends Controller
{
    public function index(Request $request)
    {
        $comments = LessonComment::query();
        $keyword = $request->get('search');

        if (!is_null($keyword)) {
            $comments = $comments->where('body', 'LIKE', "%$keyword%");
        }

        $comments = $comments->paginate(20);

        return view('admin.comment.index', [
            'comments' => $comments,
            'delete' => 'admin.cl.lesson.comment.destroy',
        ]);
    }

    public function destroy(LessonComment $comment)
    {
        $comment->delete();

        return redirect()->route('admin.cl.lesson.comment.index');
    }
}
