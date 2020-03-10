<?php

namespace App\Http\Controllers\API\Statistics;

use App\Models\CL\Course;
use App\Models\CL\Rating\RateLesson;
use App\Models\CL\TestQuestion;
use App\Models\CL\UserLesson;
use App\Models\UserResults;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SchoolStatisticsController extends Controller
{
    /**
     * @SWG\Get(
     *     path="/api/atameken/statistics/school/student",
     *     summary="Statistics of Students.",
     *     tags={"Atameken Admin"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *     @SWG\Parameter(name="Api-Key", in="header", description="Encrypted key for access", required=true, type="string"),
     *
     *      @SWG\Parameter(name="search", description="Search word", in="formData", type="string",),
     *      @SWG\Parameter(name="per_page", description="Items per page (Pagination)", in="formData", type="integer",),
     *      @SWG\Parameter(name="page", description="Page number (Pagination)", in="formData", type="integer",),
     *      @SWG\Parameter(name="order_by[column]", description="Custom orderBy (Ex: ?order_by[rating]=DESC&order_by[views]=ASC)", in="formData", type="string",),
     *
     *     @SWG\Parameter(name="school_ids", description="School Ids (Ex: [1,2,3])", in="formData", type="string",),
     *     @SWG\Parameter(name="region_ids", description="Region Ids (Ex: [1,2,3])", in="formData", type="string",),
     *     @SWG\Parameter(name="district_ids", description="District Ids (Ex: [1,2,3])", in="formData", type="string",),
     *     @SWG\Parameter(name="locality_ids", description="Locality Ids (Ex: [1,2,3])", in="formData", type="string",),
     *     @SWG\Parameter(name="class_years", description="Year of class (Ex: [10,11])", in="formData", type="string",),
     *     @SWG\Parameter(name="class_form[]", description="Form of class (Ex: class_form[0]=Б)", in="formData", type="string",),
     *
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                      @SWG\Property(property="current_page", type="integer"),
     *                      @SWG\Property(property="data",
     *                          @SWG\Items(ref="#/definitions/User")
     *                      ),
     *                      @SWG\Property(property="first_page_url", type="string"),
     *                      @SWG\Property(property="from", type="integer"),
     *                      @SWG\Property(property="last_page", type="integer"),
     *                      @SWG\Property(property="last_page_url", type="string"),
     *                      @SWG\Property(property="next_page_url", type="string"),
     *                      @SWG\Property(property="path", type="string"),
     *                      @SWG\Property(property="per_page", type="integer"),
     *                      @SWG\Property(property="prev_page_url", type="string"),
     *                      @SWG\Property(property="to", type="integer"),
     *                      @SWG\Property(property="total", type="integer"),
     *              ),
     *         ),
     *     ),
     * )
     */
    public function indexStudent(Request $request) {
        $students = User::query()
            ->select('id', 'firstname', 'lastname', 'middlename', 'phone',
                'region_id', 'district_id', 'locality_id', 'school_id', 'created_at', 'class_year', 'class_form')
            ->whereNotNull('school_id');

        $keyword = $request->get('search');
        $orderBy = $request->get('order_by');
        $school_ids = json_decode($request->get('school_ids'));
        $region_ids = json_decode($request->get('region_ids'));
        $district_ids = json_decode($request->get('district_ids'));
        $locality_ids = json_decode($request->get('locality_ids'));
        $class_years = json_decode($request->get('class_years'));
        $class_forms = $request->get('class_forms');

        if (!is_null($keyword)) {
            $students = $students->where('firstname', 'LIKE', "%$keyword%")
                ->orWhere('lastname', 'LIKE', "%$keyword%")
                ->orWhere('middlename', 'LIKE', "%$keyword%")
                ->orWhere('phone', 'LIKE', "%$keyword%");
        }
        if (!is_null($school_ids)) {
            $students = $students->whereIn('school_id', $school_ids);
        }
        if (!is_null($region_ids)) {
            $students = $students->whereIn('region_id', $region_ids);
        }
        if (!is_null($district_ids)) {
            $students = $students->whereIn('district_id', $district_ids);
        }
        if (!is_null($locality_ids)) {
            $students = $students->whereIn('locality_id', $locality_ids);
        }
        if (!is_null($class_years)) {
            $students = $students->whereIn('class_year', $class_years);
        }
        if (!is_null($class_forms)) {
            $students = $students->whereIn('class_form', $class_forms);
        }
        if (!is_null($orderBy)) {
            foreach ($orderBy as $column => $method) {
                $students = $students->orderBy($column, $method);
            }
        }

        $per_page = is_null($request->get('per_page')) ? 20 : intval($request->get('per_page'));
        $students = $students->paginate($per_page);

        $students->map(function ($user) {
            $user->append('registration_date');
            return $user;
        });

        return response()->json([
            'success' => true,
            'data' => $students
        ], 200);
    }

    /**
     * @SWG\Get(
     *     path="/api/atameken/statistics/school/student/{user_id}",
     *     summary="Student's information.",
     *     tags={"Atameken Admin"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *     @SWG\Parameter(name="Api-Key", in="header", description="Encrypted key for access", required=true, type="string"),
     *
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data",
     *                  @SWG\Items(ref="#/definitions/User")
     *              ),
     *         ),
     *     ),
     * )
     */
    public function student(User $user) {
        $user->append('registration_date');
        $user->append('achievement_points');
        $user->makeHidden('achievements');
        $user->append('region');
        $user->append('district');
        $user->append('locality');
        $user->append('school');
        $user->append('progress_in_system');

        return response()->json([
            'success' => true,
            'data' => $user
        ], 200);
    }

    /**
     * @SWG\Get(
     *     path="/api/atameken/statistics/school/student/{user_id}/course/{course_id}",
     *     summary="Student's course information.",
     *     tags={"Atameken Admin"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *     @SWG\Parameter(name="Api-Key", in="header", description="Encrypted key for access", required=true, type="string"),
     *
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                      @SWG\Property(property="course_id", type="integer",),
     *                      @SWG\Property(property="course_title", type="string"),
     *                      @SWG\Property(property="course_img_preview", type="string"),
     *                      @SWG\Property(property="avg_lessons_rate", type="integer"),
     *                      @SWG\Property(property="count_lessons_rate", type="integer"),
     *                      @SWG\Property(property="avg_tests_result", type="integer"),
     *                      @SWG\Property(property="count_tests_past", type="integer"),
     *                      @SWG\Property(property="past_lessons",
     *                          @SWG\Items(ref="#/definitions/UserLesson")
     *                      ),
     *              ),
     *         ),
     *     ),
     * )
     */
    public function studentCourse(User $user, Course $course) {
        $lessonsRate = RateLesson::query()->where('user_id', $user->id)
            ->whereIn('lesson_id', $course->lessons()->pluck('id')->toArray());
        $lessonsPast = UserLesson::query()->where('user_id', $user->id)
            ->whereIn('lesson_id', $course->lessons()->pluck('id')->toArray())
            ->where('finished', 1);
        $testsResult = UserResults::query()->where('user_id', $user->id)
            ->whereIn('lesson_id', $course->lessons()->pluck('id')->toArray());
        $pastLessons = $lessonsPast->get();

        $pastTests = $testsResult->groupBy('lesson_id')
            ->orderBy('created_at', 'DESC')
            ->get();

        $pastTests->map(function ($test) {
            $test->append('wrong_questions_models');
            return $test;
        });

        $pastLessons->map(function ($lesson) {
            $lesson->append('last_test_process');
            return $lesson;
        });

        $info = [
            'course_id' => $course->id,
            'course_title' => $course->title,
            'course_img_preview' => $course->img_src,
            'avg_lessons_rate' => round(floatval($lessonsRate->avg('value')) * 20, 2),
            'count_lessons_rate' => $lessonsRate->count(),
            'avg_tests_result' => round($testsResult->avg('result_percent'), 2),
            'count_tests_past' => count($testsResult->groupBy('lesson_id')->get()),
            'past_lessons' => $pastLessons->toArray(),
            'past_tests' => $pastTests->toArray(),
        ];

        return response()->json([
            'success' => true,
            'data' => $info
        ], 200);
    }
}
