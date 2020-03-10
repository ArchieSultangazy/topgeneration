<?php

namespace App\Http\Controllers\Admin\CL;

use App\Models\CL\Course;
use App\Models\CL\Lesson;
use App\Models\CL\Test;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class TestController extends Controller
{
    public function indexCourses(Request $request)
    {
        $courses = Course::query();
        $keyword = $request->get('search');

        if (!is_null($keyword)) {
            $courses = $courses->where('title', 'LIKE', "%$keyword%");
        }

        $courses = $courses->paginate(20);

        return view('admin.cl.test.index_courses', [
            'courses' => $courses,
        ]);
    }

    public function indexLessons(Course $course)
    {
        $lessons = $course->lessons();
        if (!$lessons->exists()) {
            return redirect(route('admin.cl.test-courses.index'));
        }
        $lessons = $lessons->paginate(20);

        return view('admin.cl.test.index_lessons', [
            'lessons' => $lessons,
            'course' => $course
        ]);
    }

    public function index(Course $course, Lesson $lesson)
    {
        $tests = $lesson->tests();

        $tests = $tests->paginate(20);

        return view('admin.cl.test.index', [
            'tests' => $tests,
            'course' => $course,
            'lesson' => $lesson,
        ]);
    }

    public function store(Lesson $lesson)
    {
        if ($lesson->tests()->exists()) {
            return redirect()->back();
        }

        $test = new Test();
        $test->lesson_id = $lesson->id;
        $test->created_user_id = Auth::user()->id;
        $test->save();

        return redirect()->back();
    }

    public function destroy(Test $test)
    {
        try {
            $test->delete();
            $test->questions->delete();
        } catch (\Exception $e) {
            \Log::info($e);
        }

        return redirect()->back();
    }
}
