<?php

namespace App\Http\Controllers\Admin\KB;

use App\Models\KB\Comment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CommentController extends Controller
{
    public function index(Request $request)
    {
        $comments = Comment::query();
        $keyword = $request->get('search');

        if (!is_null($keyword)) {
            $comments = $comments->where('body', 'LIKE', "%$keyword%");
        }

        $comments = $comments->paginate(20);

        return view('admin.comment.index', [
            'comments' => $comments,
            'delete' => 'admin.kb.comment.destroy',
        ]);
    }

    public function destroy(Comment $comment)
    {
        $comment->delete();

        return redirect()->route('admin.kb.comment.index');
    }
}
