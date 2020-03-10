<?php

namespace App\Http\Controllers\API;

use App\Achievement\Exceptions\AchievementExistsException;
use App\Achievement\Exceptions\AchievementNotExistsException;
use App\Achievement\Strategies\AddBriefAchievementStrategy;
use App\Achievement\Strategies\AddRegionIDAchievementStrategy;
use App\Achievement\Strategies\DeleteUpdatePhotoAchievementStrategy;
use App\Achievement\Strategies\FilledProfileInfoAchievementStrategy;
use App\Achievement\Strategies\UploadPhotoAchievementStrategy;
use App\Entities\Achievement;
use App\Http\Requests\API\UserRequest;
use App\Http\Controllers\Controller;
use App\Models\Job\UserJob;
use App\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * @SWG\Get(
     *     path="/api/profile/info",
     *     summary="Get User's Information.",
     *     tags={"Profile"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *     @SWG\Parameter(name="Authorization", in="header", description="Format: `Bearer <api_token>`", required=true, type="string"),
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
     *     @SWG\Response(response="401", description="Unauthenticated user",),
     * )
     */
    public function info() {
        $user = Auth::user();

        $user->append('specializations');
        $user->append('region');
        $user->append('job');

        $user->setAttribute('qa_count', $user->questions()->count() + $user->answers()->count());
        $user->setAttribute('cm_count', $user->qaComments()->count() + $user->kbComments()->count());

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user
            ]
        ], 200);
    }

    /**
     * @SWG\Post(
     *     path="/api/profile/update",
     *     summary="Update User's Information.",
     *     tags={"Profile"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *     @SWG\Parameter(name="Authorization", in="header", description="Format: `Bearer <api_token>`", required=true, type="string"),
     *
     *     @SWG\Parameter(name="firstname", description="First name", required=true, in="formData", type="string",),
     *     @SWG\Parameter(name="lastname", description="Last name", required=true, in="formData", type="string",),
     *     @SWG\Parameter(name="_method", description="_method: PUT", required=true, in="formData", type="string",),
     *
     *     @SWG\Parameter(name="email", description="Email (Unique)", required=false, in="formData", type="string",),
     *     @SWG\Parameter(name="avatar", description="Image (max: 2MB)", required=false, in="formData", type="file",),
     *     @SWG\Parameter(name="status", description="Status Field (max: 45c hars)", required=false, in="formData", type="string",),
     *     @SWG\Parameter(name="about", description="About (max: 240 chars)", required=false, in="formData", type="string",),
     *     @SWG\Parameter(name="birth_date", description="Timestamp (Ex: 1996-08-13)", required=false, in="formData", type="string",),
     *     @SWG\Parameter(name="contacts", description="Json (Ex: {'instagram':'theshakenov', 'telegram': 'shakenov'}')", required=false, in="formData", type="string",),
     *     @SWG\Parameter(name="region_id", description="Region Id", required=false, in="formData", type="integer",),
     *
     *     @SWG\Parameter(name="region_id", description="Region ID", in="formData", type="integer",),
     *     @SWG\Parameter(name="district_id", description="District ID", in="formData", type="integer",),
     *     @SWG\Parameter(name="locality_id", description="Locality ID", in="formData", type="integer",),
     *     @SWG\Parameter(name="school_id", description="School ID", in="formData", type="integer",),
     *     @SWG\Parameter(name="class_year", description="Year of class (Ex. 10, 11)", in="formData", type="integer",),
     *     @SWG\Parameter(name="class_form", description="Form of class (Ex. А, В)", in="formData", type="string",),
     *
     *     @SWG\Parameter(name="specializations", description="Json (Ex: '[134,150,185]')", required=false, in="formData", type="string",),
     *     @SWG\Parameter(name="job[name]", description="Job Name", required=false, in="formData", type="string",),
     *     @SWG\Parameter(name="job[domain_id]", description="Domain Id", required=false, in="formData", type="integer",),
     *     @SWG\Parameter(name="job[position]", description="Job position (Ex: Back-end Developer)", required=false, in="formData", type="string",),
     *     @SWG\Parameter(name="job[start_date]", description="Date (Ex: 1996-08-13)", required=false, in="formData", type="string",),
     *     @SWG\Parameter(name="job[link]", description="Job website", required=false, in="formData", type="string",),
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
    public function update(UserRequest $request)
    {
        /** @var User $user */
        $user = Auth::user();
        $data = $request->all();
        $specializations = json_decode($request->get('specializations'), true);

        if (isset($data["job"]) && empty($user->job)) {
            $data["job"]["user_id"] = $user->id;
            UserJob::create($data["job"]);
        } else if (isset($data["job"])) {
            $user->job->update($data["job"]);
        }

        if (!is_null($specializations)) {
            $user->specializations()->detach();
            foreach ($specializations as $specialization) {
                $user->specializations()->attach($specialization);
            }
        }

        $uploadPhotoAchievementStrategy = new UploadPhotoAchievementStrategy($user);
        $context = new Achievement($uploadPhotoAchievementStrategy);

        if ($request->hasFile('avatar')) {
            Storage::disk('user')->delete($user->avatar);
            $data['avatar'] = Storage::disk('user')->putFileAs(
                $user->id, $data['avatar'], 'avatar_' . time() . '.' . $data['avatar']->getClientOriginalExtension()
            );

            try {
                $context->run();
            } catch (AchievementExistsException $e) {
                \Log::info($e);
            } catch (\Exception $e) {
                \Log::info($e);
            }
        }

        $context->setNext(new AddRegionIDAchievementStrategy($user, $request));

        try {
            $context->run();
        } catch (\Exception $e) {
            \Log::info($e);
        }


        $context->setNext(new AddBriefAchievementStrategy($user, $request));

        try {
            $context->run();
        } catch (AchievementExistsException $e) {
            \Log::info($e);
        } catch (\Exception $e) {
            \Log::info($e);
        }

        $context->setNext(new FilledProfileInfoAchievementStrategy($user, $request));

        try {
            $context->run();
        } catch (AchievementExistsException $e) {
            \Log::info($e);
        } catch (\Exception $e) {
            \Log::info($e);
        }

        if (!is_null($request->get('type'))) {
            $user->accessGroup()->detach();
            $user->accessGroup()->attach($request->get('type'));
        }
        if (isset($data['class_form'])) {
            $data['class_form'] = mb_strtoupper($data['class_form']);
        }

        $user->update($data);

        return response()->json(['success' => true, 'data' => ['message' => 'Your information updated successfully.']], 200);
    }

    /**
     * @SWG\Get(
     *     path="/api/profile/questions",
     *     summary="Get user's questions.",
     *     tags={"Profile"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *     @SWG\Parameter(name="Authorization", in="header", description="Format: `Bearer <api_token>`", required=true, type="string"),
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
    public function questions(Request $request)
    {
        $user = Auth::user();
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
     *     path="/api/profile/answers",
     *     summary="Get user's answers.",
     *     tags={"Profile"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *     @SWG\Parameter(name="Authorization", in="header", description="Format: `Bearer <api_token>`", required=true, type="string"),
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
    public function answers(Request $request)
    {
        $user = Auth::user();
        $per_page = is_null($request->get('per_page')) ? 20 : intval($request->get('per_page'));

        return response()->json([
            'success' => true,
            'data' => [
                'answers' => $user->answers()->orderBy('created_at', 'DESC')->paginate($per_page),
            ]], 200);
    }

    /**
     * @SWG\Get(
     *     path="/api/profile/qa/comments",
     *     summary="Get user's Question comments.",
     *     tags={"Profile"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *     @SWG\Parameter(name="Authorization", in="header", description="Format: `Bearer <api_token>`", required=true, type="string"),
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
    public function qaComments(Request $request)
    {
        $user = Auth::user();
        $per_page = is_null($request->get('per_page')) ? 20 : intval($request->get('per_page'));

        return response()->json([
            'success' => true,
            'data' => [
                'comments' => $user->qaComments()->paginate($per_page),
            ]], 200);
    }

    /**
     * @SWG\Get(
     *     path="/api/profile/kb/comments",
     *     summary="Get user's Article comments.",
     *     tags={"Profile"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *     @SWG\Parameter(name="Authorization", in="header", description="Format: `Bearer <api_token>`", required=true, type="string"),
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
    public function kbComments(Request $request)
    {
        $user = Auth::user();
        $per_page = is_null($request->get('per_page')) ? 20 : intval($request->get('per_page'));

        return response()->json([
            'success' => true,
            'data' => [
                'comments' => $user->kbComments()->paginate($per_page),
            ]], 200);
    }

    /**
     * @SWG\Get(
     *     path="/api/profile/qa/favorite",
     *     summary="Get user's fvorite Questions.",
     *     tags={"Profile"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *     @SWG\Parameter(name="Authorization", in="header", description="Format: `Bearer <api_token>`", required=true, type="string"),
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
    public function qaFavorite(Request $request)
    {
        $user = Auth::user();
        $per_page = is_null($request->get('per_page')) ? 20 : intval($request->get('per_page'));
        $questions = $user->favQuestions()->paginate($per_page);

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
     *     path="/api/profile/kb/favorite",
     *     summary="Get user's fvorite Articles.",
     *     tags={"Profile"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *     @SWG\Parameter(name="Authorization", in="header", description="Format: `Bearer <api_token>`", required=true, type="string"),
     *
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="articles",
     *                      @SWG\Property(property="current_page", type="integer"),
     *                      @SWG\Property(property="data",
     *                          @SWG\Items(ref="#/definitions/Article")
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
    public function kbFavorite(Request $request)
    {
        $user = Auth::user();
        $per_page = is_null($request->get('per_page')) ? 20 : intval($request->get('per_page'));
        $articles = $user->favArticles()->paginate($per_page);

        $articles->map(function ($article) {
            $article->append('fav_count');
            $article->append('themes');
            $article->makeHidden('body');
            return $article;
        });

        return response()->json([
            'success' => true,
            'data' => [
                'articles' => $articles,
            ]], 200);
    }

    /**
     * @SWG\Get(
     *     path="/api/profile/cl/courses",
     *     summary="Get user's courses.",
     *     tags={"Profile"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *     @SWG\Parameter(name="Authorization", in="header", description="Format: `Bearer <api_token>`", required=true, type="string"),
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
    public function courses(Request $request)
    {
        $user = Auth::user();
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
     * @SWG\Delete(
     *     path="/api/profile/avatar/delete",
     *     summary="Delete avatar.",
     *     tags={"Profile"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *     @SWG\Parameter(name="Authorization", in="header", description="Format: `Bearer <api_token>`", required=true, type="string"),
     *
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="message", type="string"),
     *              ),
     *         ),
     *     ),
     *     @SWG\Response(response="400", description="Bad request. User has no photo.",
     *         @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="message", type="string",),
     *              ),
     *         ),
     *     ),
     * )
     */

    /**
     * @return \Illuminate\Http\JsonResponse
     * @throws AchievementExistsException
     */
    public function deleteUserAvatar()
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user->avatar) {
            return response()->json([
                'success' => false,
                'data' => [
                    'message' => 'User has no photo.'
                ],
            ],Response::HTTP_BAD_REQUEST);
        }

        Storage::disk('user')->delete($user->avatar);

        $user->avatar = null;
        $user->save();

        $deleteUpdatePhotoAchievementStrategy = new DeleteUpdatePhotoAchievementStrategy($user);
        $context = new Achievement($deleteUpdatePhotoAchievementStrategy);

        try {
            $context->run();
        } catch (AchievementNotExistsException $e) {
            \Log::info($e);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'message' => 'User photo has been deleted',
            ],
        ],Response::HTTP_OK);
    }
}
