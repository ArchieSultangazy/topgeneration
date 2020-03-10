<?php

namespace App\Http\Controllers\API;

use App\Http\Resources\UserAchievementResource;
use App\Http\Resources\UserProfileProgressResource;
use App\Models\Achievement;
use App\Models\Job\SpecializationApprover;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * @SWG\Get(
     *     path="/api/user/info",
     *     summary="Get User's Mini Information.",
     *     tags={"User"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *     @SWG\Parameter(name="Authorization", in="header", description="Format: `Bearer <api_token>`", required=true, type="string"),
     *
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="user",
     *                      @SWG\Property(property="id", type="integer"),
     *                      @SWG\Property(property="firstname", type="string"),
     *                      @SWG\Property(property="lastname", type="string"),
     *                      @SWG\Property(property="avatar_src", type="string"),
     *                  ),
     *              ),
     *         ),
     *     ),
     *     @SWG\Response(response="401", description="Unauthenticated user",),
     * )
     */
    public function info()
    {
        $user = Auth::user();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'firstname' => $user->firstname,
                    'lastname' => $user->lastname,
                    'avatar_src' => $user->avatar_src,
                ],
            ]], 200);
    }

    /**
     * @SWG\Get(
     *     path="/api/user/{user_id}/info",
     *     summary="Get User's Mini Info by ID.",
     *     tags={"User"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="user",
     *                      @SWG\Property(property="id", type="integer"),
     *                      @SWG\Property(property="firstname", type="string"),
     *                      @SWG\Property(property="lastname", type="string"),
     *                      @SWG\Property(property="avatar_src", type="string"),
     *                  ),
     *              ),
     *         ),
     *     ),
     * )
     */
    public function userInfo(User $user)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'firstname' => $user->firstname,
                    'lastname' => $user->lastname,
                    'avatar_src' => $user->avatar_src,
                    'achievements_points' => $user->achievement_points,
                ],
            ]], 200);
    }

    /**
     * @SWG\Get(
     *     path="/api/user/{user_id}",
     *     summary="Get User by ID.",
     *     tags={"User"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="user",
     *                      @SWG\Items(ref="#/definitions/User")
     *                  ),
     *                  @SWG\Property(property="specializations",
     *                      @SWG\Items(ref="#/definitions/Specialization")
     *                  ),
     *                  @SWG\Property(property="region",
     *                      @SWG\Items(ref="#/definitions/Region")
     *                  ),
     *                  @SWG\Property(property="job",
     *                      @SWG\Items(ref="#/definitions/UserJob")
     *                  ),
     *              ),
     *         ),
     *     ),
     * )
     */
    public function user(User $user)
    {
        $user->append('specializations');
        $user->append('region');
        $user->append('job');

        $user->setAttribute('qa_count', $user->questions()->count() + $user->answers()->count());
        $user->setAttribute('cm_count', $user->qaComments()->count() + $user->kbComments()->count());

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user
            ]], 200);
    }

    /**
     * @SWG\Get(
     *     path="/api/user/{user_id}/questions",
     *     summary="Get user's questions.",
     *     tags={"User"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="questions",
     *                      @SWG\Property(property="current_page", type="integer"),
     *                      @SWG\Property(property="data",
     *                          @SWG\Items(ref="#/definitions/Question")
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
     *                  ),
     *              ),
     *         ),
     *     ),
     * )
     */
    public function questions(User $user, Request $request)
    {
        $per_page = is_null($request->get('per_page')) ? 20 : intval($request->get('per_page'));
        $questions = $user->questions()->orderBy('created_at', 'DESC')->paginate($per_page);

        $questions->map(function ($question) {
            $question->append('themes');
            $question->append('fav_count');
            $question->append('answers_count');
            return $question;
        });

        return response()->json([
            'success' => true,
            'data' => [
                'questions' => $questions,
            ]], 200);
    }

    /**
     * @SWG\Get(
     *     path="/api/user/{user_id}/answers",
     *     summary="Get user's answers.",
     *     tags={"User"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="answers",
     *                      @SWG\Property(property="current_page", type="integer"),
     *                      @SWG\Property(property="data",
     *                          @SWG\Items(ref="#/definitions/Answer")
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
     *                  ),
     *              ),
     *         ),
     *     ),
     * )
     */
    public function answers(User $user, Request $request)
    {
        $per_page = is_null($request->get('per_page')) ? 20 : intval($request->get('per_page'));

        return response()->json([
            'success' => true,
            'data' => [
                'answers' => $user->answers()->orderBy('created_at', 'DESC')->paginate($per_page),
            ]], 200);
    }

    /**
     * @SWG\Get(
     *     path="/api/user/{user_id}/qa/comments",
     *     summary="Get user's Question comments.",
     *     tags={"User"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="comments",
     *                      @SWG\Property(property="current_page", type="integer"),
     *                      @SWG\Property(property="data",
     *                          @SWG\Items(ref="#/definitions/Comment")
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
     *                  ),
     *              ),
     *         ),
     *     ),
     * )
     */
    public function qaComments(User $user, Request $request)
    {
        $per_page = is_null($request->get('per_page')) ? 20 : intval($request->get('per_page'));

        return response()->json([
            'success' => true,
            'data' => [
                'comments' => $user->qaComments()->paginate($per_page),
            ]], 200);
    }

    /**
     * @SWG\Get(
     *     path="/api/user/{user_id}/kb/comments",
     *     summary="Get user's Article comments.",
     *     tags={"User"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="comments",
     *                      @SWG\Property(property="current_page", type="integer"),
     *                      @SWG\Property(property="data",
     *                          @SWG\Items(ref="#/definitions/KBComment")
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
     *                  ),
     *              ),
     *         ),
     *     ),
     * )
     */
    public function kbComments(User $user, Request $request)
    {
        $per_page = is_null($request->get('per_page')) ? 20 : intval($request->get('per_page'));

        return response()->json([
            'success' => true,
            'data' => [
                'comments' => $user->kbComments()->paginate($per_page),
            ]], 200);
    }

    /**
     * @SWG\Get(
     *     path="/api/user/{user_id}/cl/courses",
     *     summary="Get user's courses.",
     *     tags={"User"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="articles",
     *                      @SWG\Property(property="current_page", type="integer"),
     *                      @SWG\Property(property="data",
     *                          @SWG\Items(ref="#/definitions/Course")
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
     *                  ),
     *              ),
     *         ),
     *     ),
     * )
     */
    public function courses(User $user, Request $request)
    {
        $per_page = is_null($request->get('per_page')) ? 20 : intval($request->get('per_page'));
        $courses = $user->courses()->paginate($per_page);

        $courses->map(function ($course) {
            $course->append('user_course');
            return $course;
        });

        return response()->json([
            'success' => true,
            'data' => [
                'courses' => $courses,
            ]], 200);
    }

    /**
     * @SWG\Post(
     *     path="/api/user/specialization/approve",
     *     summary="Approve user's specialization.",
     *     tags={"User"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *     @SWG\Parameter(name="Authorization", in="header", description="Format: `Bearer <api_token>`", required=true, type="string"),
     *
     *     @SWG\Parameter(name="user_id", description="Other user's ID", required=true, in="formData", type="integer",),
     *     @SWG\Parameter(name="specialization_id", description="Specialization ID", required=true, in="formData", type="integer",),
     *
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="message", type="string",),
     *              ),
     *         ),
     *     ),
     *     @SWG\Response(response="422", description="Validation failed",
     *         @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="errors", type="object",),
     *              ),
     *         ),
     *     ),
     * )
     */
    public function approveUserSpecialization(Request $request)
    {
        $userId = Auth::guard('api')->id();
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|numeric',
            'specialization_id' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'data' => ['errors' => $validator->errors()]], 422);
        }

        $specApprover = SpecializationApprover::where('user_id', $request->get('user_id'))
            ->where('specialization_id', $request->get('specialization_id'))
            ->where('approver_id', $userId)
            ->first();

        if (is_null($specApprover)) {
            SpecializationApprover::create([
                'user_id' => $request->get('user_id'),
                'specialization_id' => $request->get('specialization_id'),
                'approver_id' => $userId,
            ]);
        }

        return response()->json(['success' => true, 'data' => ['message' => 'Specialization Approved successfully.']], 200);
    }

    /**
     * @SWG\Post(
     *     path="/api/user/specialization/disapprove",
     *     summary="Disapprove user's specialization.",
     *     tags={"User"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *     @SWG\Parameter(name="Authorization", in="header", description="Format: `Bearer <api_token>`", required=true, type="string"),
     *
     *     @SWG\Parameter(name="user_id", description="Other user's ID", required=true, in="formData", type="integer",),
     *     @SWG\Parameter(name="specialization_id", description="Specialization ID", required=true, in="formData", type="integer",),
     *
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="message", type="string",),
     *              ),
     *         ),
     *     ),
     *     @SWG\Response(response="422", description="Validation failed",
     *         @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="errors", type="object",),
     *              ),
     *         ),
     *     ),
     * )
     */
    public function disapproveUserSpecialization(Request $request)
    {
        $userId = Auth::guard('api')->id();
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|numeric',
            'specialization_id' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'data' => ['errors' => $validator->errors()]], 422);
        }

        $specApprover = SpecializationApprover::where('user_id', $request->get('user_id'))
            ->where('specialization_id', $request->get('specialization_id'))
            ->where('approver_id', $userId)
            ->first();

        if (!is_null($specApprover)) {
            $specApprover->delete();
        }

        return response()->json(['success' => true, 'data' => ['message' => 'Specialization Disapproved successfully.']], 200);
    }

    //TODO::Вытащить функций в отдельный контроллер

    /**
     * @SWG\Post(
     *     path="/api/user/fav/question/add",
     *     summary="Add question to user's favorites.",
     *     tags={"QA (Question)"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *     @SWG\Parameter(name="Authorization", in="header", description="Format: `Bearer <api_token>`", required=true, type="string"),
     *
     *     @SWG\Parameter(name="question_id", description="Question ID", required=true, in="formData", type="integer",),
     *
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="message", type="string",),
     *              ),
     *         ),
     *     ),
     *     @SWG\Response(response="422", description="Validation failed",
     *         @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="errors", type="object",),
     *              ),
     *         ),
     *     ),
     * )
     */
    public function addFavQuestion(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'question_id' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'data' => ['errors' => $validator->errors()]], 422);
        }

        $userFavQuestions = Auth::user()->favQuestions();

        if (!in_array($request->get('question_id'), $userFavQuestions->pluck('question_id')->toArray())) {
            $userFavQuestions->attach($request->get('question_id'));
        }

        return response()->json(['success' => true, 'data' => ['message' => 'Question added to favorites successfully.']], 200);
    }

    /**
     * @SWG\Post(
     *     path="/api/user/fav/question/remove",
     *     summary="Remove question to user's favorites.",
     *     tags={"QA (Question)"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *     @SWG\Parameter(name="Authorization", in="header", description="Format: `Bearer <api_token>`", required=true, type="string"),
     *
     *     @SWG\Parameter(name="question_id", description="Question ID", required=true, in="formData", type="integer",),
     *
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="message", type="string",),
     *              ),
     *         ),
     *     ),
     *     @SWG\Response(response="422", description="Validation failed",
     *         @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="errors", type="object",),
     *              ),
     *         ),
     *     ),
     * )
     */
    public function removeFavQuestion(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'question_id' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'data' => ['errors' => $validator->errors()]], 422);
        }

        $userFavQuestions = Auth::user()->favQuestions();

        if (in_array($request->get('question_id'), $userFavQuestions->pluck('question_id')->toArray())) {
            $userFavQuestions->detach($request->get('question_id'));
        }

        return response()->json(['success' => true, 'data' => ['message' => 'Question removed from favorites successfully.']], 200);
    }

    /**
     * @SWG\Post(
     *     path="/api/user/fav/theme/add",
     *     summary="Add theme to user's favorites.",
     *     tags={"QA"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *     @SWG\Parameter(name="Authorization", in="header", description="Format: `Bearer <api_token>`", required=true, type="string"),
     *
     *     @SWG\Parameter(name="theme_id", description="Theme ID", required=true, in="formData", type="integer",),
     *
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="message", type="string",),
     *              ),
     *         ),
     *     ),
     *     @SWG\Response(response="422", description="Validation failed",
     *         @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="errors", type="object",),
     *              ),
     *         ),
     *     ),
     * )
     */
    public function addFavQATheme(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'theme_id' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'data' => ['errors' => $validator->errors()]], 422);
        }

        $userFavThemes = Auth::user()->favQAThemes();

        if (!in_array($request->get('theme_id'), $userFavThemes->pluck('theme_id')->toArray())) {
            $userFavThemes->attach($request->get('theme_id'));
        }

        return response()->json(['success' => true, 'data' => ['message' => 'Theme added to favorites successfully.']], 200);
    }

    /**
     * @SWG\Post(
     *     path="/api/user/fav/theme/remove",
     *     summary="Remove theme to user's favorites.",
     *     tags={"QA"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *     @SWG\Parameter(name="Authorization", in="header", description="Format: `Bearer <api_token>`", required=true, type="string"),
     *
     *     @SWG\Parameter(name="theme_id", description="Theme ID", required=true, in="formData", type="integer",),
     *
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="message", type="string",),
     *              ),
     *         ),
     *     ),
     *     @SWG\Response(response="422", description="Validation failed",
     *         @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="errors", type="object",),
     *              ),
     *         ),
     *     ),
     * )
     */
    public function removeFavQATheme(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'theme_id' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'data' => ['errors' => $validator->errors()]], 422);
        }

        $userFavThemes = Auth::user()->favQAThemes();

        if (in_array($request->get('theme_id'), $userFavThemes->pluck('theme_id')->toArray())) {
            $userFavThemes->detach($request->get('theme_id'));
        }

        return response()->json(['success' => true, 'data' => ['message' => 'Theme removed to favorites successfully.']], 200);
    }

    /**
     * @SWG\Post(
     *     path="/api/user/fav/article/add",
     *     summary="Add article to user's favorites.",
     *     tags={"KB (Article)"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *     @SWG\Parameter(name="Authorization", in="header", description="Format: `Bearer <api_token>`", required=true, type="string"),
     *
     *     @SWG\Parameter(name="article_id", description="Article ID", required=true, in="formData", type="integer",),
     *
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="message", type="string",),
     *              ),
     *         ),
     *     ),
     *     @SWG\Response(response="422", description="Validation failed",
     *         @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="errors", type="object",),
     *              ),
     *         ),
     *     ),
     * )
     */
    public function addFavArticle(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'article_id' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'data' => ['errors' => $validator->errors()]], 422);
        }

        $userFavArticles = Auth::user()->favArticles();

        if (!in_array($request->get('article_id'), $userFavArticles->pluck('article_id')->toArray())) {
            $userFavArticles->attach($request->get('article_id'));
        }

        return response()->json(['success' => true, 'data' => ['message' => 'Article added to favorites successfully.']], 200);
    }

    /**
     * @SWG\Post(
     *     path="/api/user/fav/article/remove",
     *     summary="Remove article from user's favorites.",
     *     tags={"KB (Article)"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *     @SWG\Parameter(name="Authorization", in="header", description="Format: `Bearer <api_token>`", required=true, type="string"),
     *
     *     @SWG\Parameter(name="article_id", description="Article ID", required=true, in="formData", type="integer",),
     *
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="message", type="string",),
     *              ),
     *         ),
     *     ),
     *     @SWG\Response(response="422", description="Validation failed",
     *         @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="errors", type="object",),
     *              ),
     *         ),
     *     ),
     * )
     */
    public function removeFavArticle(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'article_id' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'data' => ['errors' => $validator->errors()]], 422);
        }

        $userFavArticles = Auth::user()->favArticles();

        if (in_array($request->get('article_id'), $userFavArticles->pluck('article_id')->toArray())) {
            $userFavArticles->detach($request->get('article_id'));
        }

        return response()->json(['success' => true, 'data' => ['message' => 'Article removed from favorites successfully.']], 200);
    }

    /**
     * @SWG\Post(
     *     path="/api/user/fav/kb-theme/add",
     *     summary="Add theme to user's favorites.",
     *     tags={"KB"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *     @SWG\Parameter(name="Authorization", in="header", description="Format: `Bearer <api_token>`", required=true, type="string"),
     *
     *     @SWG\Parameter(name="theme_id", description="Theme ID", required=true, in="formData", type="integer",),
     *
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="message", type="string",),
     *              ),
     *         ),
     *     ),
     *     @SWG\Response(response="422", description="Validation failed",
     *         @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="errors", type="object",),
     *              ),
     *         ),
     *     ),
     * )
     */
    public function addFavKBTheme(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'theme_id' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'data' => ['errors' => $validator->errors()]], 422);
        }

        $userFavThemes = Auth::user()->favKBThemes();

        if (!in_array($request->get('theme_id'), $userFavThemes->pluck('theme_id')->toArray())) {
            $userFavThemes->attach($request->get('theme_id'));
        }

        return response()->json(['success' => true, 'data' => ['message' => 'Theme added to favorites successfully.']], 200);
    }

    /**
     * @SWG\Post(
     *     path="/api/user/fav/kb-theme/remove",
     *     summary="Remove theme to user's favorites.",
     *     tags={"KB"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *     @SWG\Parameter(name="Authorization", in="header", description="Format: `Bearer <api_token>`", required=true, type="string"),
     *
     *     @SWG\Parameter(name="theme_id", description="Theme ID", required=true, in="formData", type="integer",),
     *
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="message", type="string",),
     *              ),
     *         ),
     *     ),
     *     @SWG\Response(response="422", description="Validation failed",
     *         @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="errors", type="object",),
     *              ),
     *         ),
     *     ),
     * )
     */
    public function removeFavKBTheme(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'theme_id' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'data' => ['errors' => $validator->errors()]], 422);
        }

        $userFavThemes = Auth::user()->favKBThemes();

        if (in_array($request->get('theme_id'), $userFavThemes->pluck('theme_id')->toArray())) {
            $userFavThemes->detach($request->get('theme_id'));
        }

        return response()->json(['success' => true, 'data' => ['message' => 'Theme removed to favorites successfully.']], 200);
    }

    /**
     * @SWG\Get(
     *     path="/api/user/achievements/{userID}",
     *     summary="Get user's achievements",
     *     tags={"User"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *     @SWG\Parameter(name="Authorization", in="header", description="Format: `Bearer <api_token>`", required=true, type="string"),
     *     @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="userID",type="integer"),
     *                  @SWG\Property(property="achievements",type="array",
     *                      @SWG\Items(
     *                           @SWG\Property(property="id", type="integer"),
     *                           @SWG\Property(property="ruName", type="string"),
     *                           @SWG\Property(property="kkName", type="string"),
     *                           @SWG\Property(property="enName", type="string"),
     *                           @SWG\Property(property="key", type="string"),
     *                           @SWG\Property(property="points", type="integer"),
     *                           @SWG\Property(property="createdAt", type="string"),
     *                           @SWG\Property(property="updatedAt", type="string"),
     *                           @SWG\Property(property="deletedAt", type="string"),
     *                      ),
     *                  ),
     *                  @SWG\Property(property="createdAt", type="string"),
     *                  @SWG\Property(property="updatedAt", type="string"),
     *                  @SWG\Property(property="deletedAt", type="string"),
     *              ),
     *         ),
     *     ),
     *     @SWG\Response(response=400, description="Bad request",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="object"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="message",type="string"),
     *              ),
     *          ),
     *     ),
     * )
     */

    /**
     * @param $userID
     * @return UserAchievementResource
     */
    public function getAchievements($userID)
    {
        $user = User::where('id', $userID)
            ->with(['achievements.achievement'])
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'data' => [
                    'message' => 'User does not exist.'
                ],
            ], Response::HTTP_BAD_REQUEST);
        }

        return new UserAchievementResource($user);
    }

    /**
     * @SWG\Get(
     *     path="/api/user/achievements/profile-fill-progress/get",
     *     summary="Get user's fill profile progress",
     *     tags={"User"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *     @SWG\Parameter(name="Authorization", in="header", description="Format: `Bearer <api_token>`", required=true, type="string"),
     *     @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="data", type="array",
     *                  @SWG\Items(
     *                      @SWG\Property(property="id", type="integer"),
     *                      @SWG\Property(property="ruName", type="string"),
     *                      @SWG\Property(property="kkName", type="string"),
     *                      @SWG\Property(property="enName", type="string"),
     *                      @SWG\Property(property="key", type="string"),
     *                      @SWG\Property(property="points", type="integer"),
     *                      @SWG\Property(property="isAchieved", type="boolean"),
     *                      @SWG\Property(property="createdAt", type="string"),
     *                      @SWG\Property(property="updatedAt", type="string"),
     *                      @SWG\Property(property="deletedAt", type="string"),
     *                  ),
     *              ),
     *         ),
     *     ),
     *     @SWG\Response(response=401, description="Unauthorized",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="object"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="message",type="string"),
     *              ),
     *          ),
     *     ),
     * )
     */

    /**
     * @param $userID
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
     *
     */
    public function getProgress()
    {
        /** @var User $user */
        if (!$user = Auth::guard('api')->user()) {
            return response()->json([
                'success' => false,
                'data' => [
                    'message' => 'Unauthorized'
                ],
            ], Response::HTTP_UNAUTHORIZED);
        }

        /** @var Collection $userFillProfileProgress */
        $userFillProfileProgress = Achievement::whereIn('key', Achievement::$userProfileAchievements)
            ->orderBy('id', 'DESC')
            ->get();

        $achievements = $user->achievements;

        $result = $userFillProfileProgress->map(function ($value, $key) use ($achievements) {
            $result = false;

            if ($achievements->contains('achievement_id', $value->id)) {
                $result = true;
            }

            $value->isAchieved = $result;

            return $value;
        });

        return UserProfileProgressResource::collection($result);
    }
}