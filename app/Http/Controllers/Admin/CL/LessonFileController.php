<?php

namespace App\Http\Controllers\Admin\CL;

use App\Models\CL\Lesson;
use App\Models\CL\LessonFile;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class LessonFileController extends Controller
{
    public function index(Request $request)
    {
        $lessonsFiles = LessonFile::query();
        $keyword = $request->get('search');

        if (!is_null($keyword)) {
            $lessonsFiles = $lessonsFiles->where('title', 'LIKE', "%$keyword%");
        }

        $lessonsFiles = $lessonsFiles->paginate(20);

        return view('admin.cl.lesson.file.index', [
            'lessonsFiles' => $lessonsFiles,
        ]);
    }

    public function create()
    {
        $lessons = Lesson::all()->pluck('title', 'id')->toArray();

        return view('admin.cl.lesson.file.create', [
            'lessonsFile' => LessonFile::class,
            'lessons' => $lessons,
        ]);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'lesson_id' => 'required|numeric',
            'title' => 'required',
            'body' => 'required',
            'link' => 'required',
        ]);

        $data = $request->all();
        $lessonFile = LessonFile::create($data);

        if (is_file($data['link'])) {
            $data['link'] = Storage::disk('cl_lesson')->putFileAs(
                $request->get('lesson_id'), $data['link'],
                'file_' . $lessonFile->id . '_' . time() . '.' . $data['link']->getClientOriginalExtension()
            );
        }

        $lessonFile->update($data);

        return redirect()->route('admin.cl.file.edit', ['file' => $lessonFile]);
    }

    public function edit(LessonFile $file)
    {
        $lessons = Lesson::all()->pluck('title', 'id')->toArray();

        return view('admin.cl.lesson.file.edit', [
            'lessonsFile' => $file,
            'lessons' => $lessons,
        ]);
    }

    public function update(LessonFile $file, Request $request)
    {
        $this->validate($request, [
            'lesson_id' => 'required|numeric',
            'title' => 'required',
            'body' => 'required',
            'link' => 'required',
        ]);

        $data = $request->all();

        if (is_file($data['link'])) {
            Storage::disk('cl_lesson')->delete($file->link);
            $data['link'] = Storage::disk('cl_lesson')->putFileAs(
                $request->get('lesson_id'), $data['link'],
                'file_' . $file->id . '_' . time() . '.' . $data['link']->getClientOriginalExtension()
            );
        }

        $file->update($data);

        return redirect()->route('admin.cl.file.edit', ['file' => $file]);
    }

    public function destroy(LessonFile $file)
    {
        Storage::disk('cl_lesson')->delete($file->link);
        $file->delete();

        return redirect()->route('admin.cl.file.index');
    }
}
