<?php

namespace App\Http\Controllers\Admin\CL;

use App\Http\Requests\CL\LessonRequest;
use App\Models\CL\Course;
use App\Models\CL\Lesson;
use App\Models\KB\Article;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class LessonController extends Controller
{
    public function index(Request $request)
    {
        $lessons = Lesson::query();
        $keyword = $request->get('search');

        if (!is_null($keyword)) {
            $lessons = $lessons->where('title', 'LIKE', "%$keyword%");
        }

        $lessons = $lessons->paginate(20);

        return view('admin.cl.lesson.index', [
            'lessons' => $lessons,
        ]);
    }

    public function create()
    {
        $courses = Course::all()->pluck('title', 'id')->toArray();
        $articles = Article::all()->pluck('title', 'id')->toArray();

        return view('admin.cl.lesson.create', [
            'lesson' => Lesson::class,
            'courses' => $courses,
            'articles' => $articles,
        ]);
    }

    public function store(LessonRequest $request)
    {
        $data = $request->all();
        if (isset($data['articles'])) {
            $data['articles'] = json_encode($data['articles']);
        }
        $lesson = Lesson::create($data);

        if ($request->hasFile('img_preview')) {
            $data['img_preview'] = Storage::disk('cl_lesson')->putFileAs(
                $lesson->id, $data['img_preview'],
                'img_preview_' . time() . '.' . $data['img_preview']->getClientOriginalExtension()
            );
        }

        if ($request->get('video_type') == 'file' && $request->hasFile('video_file')) {
            $data['video'] = Storage::disk('cl_lesson')->putFileAs(
                $lesson->id, $data['video_file'],
                'video_' . time() . '.' . $data['video_file']->getClientOriginalExtension()
            );

            $getID3 = new \getID3;
            $file = $getID3->analyze($request->file('video_file'));
            $data['duration'] = $file['playtime_seconds'];
        } else if ($request->get('video_type') == 'url' && $request->has('video_url')) {
            $data['video'] = $data['video_url'];
        }

        $lesson->update($data);

        return redirect()->route('admin.cl.lesson.edit', ['lesson' => $lesson]);
    }

    public function edit(Lesson $lesson)
    {
        $courses = Course::all()->pluck('title', 'id')->toArray();
        $articles = Article::all()->pluck('title', 'id')->toArray();

        return view('admin.cl.lesson.edit', [
            'lesson' => $lesson,
            'courses' => $courses,
            'articles' => $articles,
        ]);
    }

    public function update(Lesson $lesson, LessonRequest $request)
    {
        $data = $request->all();
        if (isset($data['articles'])) {
            $data['articles'] = json_encode($data['articles']);
        }

        if ($request->hasFile('img_preview')) {
            Storage::disk('cl_lesson')->delete($lesson->img_preview);
            $data['img_preview'] = Storage::disk('cl_lesson')->putFileAs(
                $lesson->id, $data['img_preview'],
                'img_preview_' . time() . '.' . $data['img_preview']->getClientOriginalExtension()
            );
        }

        if ($request->get('video_type') == 'file' && $request->hasFile('video_file')) {
            Storage::disk('cl_lesson')->delete($lesson->video);
            $data['video'] = Storage::disk('cl_lesson')->putFileAs(
                $lesson->id, $data['video_file'],
                'video_' . time() . '.' . $data['video_file']->getClientOriginalExtension()
            );

            $getID3 = new \getID3;
            $file = $getID3->analyze($request->file('video_file'));
            $data['duration'] = $file['playtime_seconds'];
        } else if ($request->get('video_type') == 'url' && $request->has('video_url') && strpos($data['video_url'], 'http') !== false) {
            Storage::disk('cl_lesson')->delete($lesson->video);
            $data['video'] = $data['video_url'];
        }

        $lesson->update($data);

        return redirect()->route('admin.cl.lesson.edit', ['lesson' => $lesson]);
    }

    public function destroy(Lesson $lesson)
    {
        //Storage::disk('cl_lesson')->deleteDirectory($lesson->id);
        $lesson->delete();

        return redirect()->route('admin.cl.lesson.index');
    }
}
