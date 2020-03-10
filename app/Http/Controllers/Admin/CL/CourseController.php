<?php

namespace App\Http\Controllers\Admin\CL;

use App\Http\Requests\CL\CourseRequest;
use App\Models\CL\Author;
use App\Models\CL\Course;
use App\Models\CL\Theme;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $courses = Course::query();
        $keyword = $request->get('search');

        if (!is_null($keyword)) {
            $courses = $courses->where('title', 'LIKE', "%$keyword%");
        }

        $courses = $courses->paginate(20);

        return view('admin.cl.course.index', [
            'courses' => $courses,
        ]);
    }

    public function create()
    {
        $statuses = Course::getAvailableStatuses();
        $themes = Theme::all()->pluck('name', 'id')->toArray();
        $authors = Author::query()
            ->select('id', DB::raw("CONCAT(firstname,' ',lastname)  AS fullname"))
            ->pluck('fullname', 'id')
            ->toArray();

        return view('admin.cl.course.create', [
            'course' => Course::class,
            'statuses' => $statuses,
            'themes' => $themes,
            'authors' => $authors,
        ]);
    }

    public function store(CourseRequest $request)
    {
        $data = $request->all();
        $course = Course::create($data);

        foreach ($data['themes'] as $themeId) {
            $course->themes()->attach($themeId);
        }

        foreach ($data['authors'] as $authorId) {
            $course->authors()->attach($authorId);
        }

        if ($request->hasFile('img_preview')) {
            $data['img_preview'] = Storage::disk('cl_course')->putFileAs(
                $course->id, $data['img_preview'],
                'img_preview_' . time() . '.' . $data['img_preview']->getClientOriginalExtension()
            );
        }

        if ($request->hasFile('video')) {
            $data['video'] = Storage::disk('cl_course')->putFileAs(
                $course->id, $data['video'],
                'video_' . time() . '.' . $data['video']->getClientOriginalExtension()
            );

            $getID3 = new \getID3;
            $file = $getID3->analyze($request->file('video'));
            $data['duration'] = $file['playtime_seconds'];
        }

        $course->update($data);

        return redirect()->route('admin.cl.course.edit', ['course' => $course]);
    }

    public function edit(Course $course)
    {
        $statuses = Course::getAvailableStatuses();
        $themes = Theme::all()->pluck('name', 'id')->toArray();
        $authors = Author::query()
            ->select('id', DB::raw("CONCAT(firstname,' ',lastname)  AS fullname"))
            ->pluck('fullname', 'id')
            ->toArray();
        $courseThemes = $course->themes()->pluck('id')->toArray();
        $courseAuthors = $course->authors()->pluck('id')->toArray();

        return view('admin.cl.course.edit', [
            'course' => $course,
            'statuses' => $statuses,
            'themes' => $themes,
            'authors' => $authors,
            'courseThemes' => $courseThemes,
            'courseAuthors' => $courseAuthors,
        ]);
    }

    public function update(Course $course, CourseRequest $request)
    {
        $data = $request->all();

        $course->themes()->detach();
        foreach ($data['themes'] as $themeId) {
            $course->themes()->attach($themeId);
        }

        $course->authors()->detach();
        foreach ($data['authors'] as $authorId) {
            $course->authors()->attach($authorId);
        }

        if ($request->hasFile('img_preview')) {
            Storage::disk('cl_course')->delete($course->img_preview);
            $data['img_preview'] = Storage::disk('cl_course')->putFileAs(
                $course->id, $data['img_preview'],
                'img_preview_' . time() . '.' . $data['img_preview']->getClientOriginalExtension()
            );
        }

        if ($request->hasFile('video')) {
            Storage::disk('cl_course')->delete($course->video);
            $data['video'] = Storage::disk('cl_course')->putFileAs(
                $course->id, $data['video'],
                'video_' . time() . '.' . $data['video']->getClientOriginalExtension()
            );

            $getID3 = new \getID3;
            $file = $getID3->analyze($request->file('video'));
            $data['duration'] = $file['playtime_seconds'];
        }

        $course->update($data);

        return redirect()->route('admin.cl.course.edit', ['course' => $course]);
    }

    public function destroy(Course $course)
    {
        $lessons = $course->lessons()->get();
        foreach ($lessons as $lesson) {
            //Storage::disk('cl_lesson')->deleteDirectory($lesson->id);
            $lesson->delete();
        }

        //Storage::disk('cl_course')->deleteDirectory($course->id);
        $course->delete();

        return redirect()->route('admin.cl.course.index');
    }
}
