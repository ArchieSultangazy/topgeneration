<?php

namespace App\Http\Controllers\Admin\CL;

use App\Models\CL\CourseComment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CourseCommentController extends Controller
{
    public function index(Request $request)
    {
        $comments = CourseComment::query();
        $keyword = $request->get('search');

        if (!is_null($keyword)) {
            $comments = $comments->where('body', 'LIKE', "%$keyword%");
        }

        $comments = $comments->paginate(20);

        return view('admin.comment.index', [
            'comments' => $comments,
            'delete' => 'admin.cl.course.comment.destroy',
        ]);
    }

    public function destroy(CourseComment $comment)
    {
        $comment->delete();

        return redirect()->route('admin.cl.course.comment.index');
    }
}
