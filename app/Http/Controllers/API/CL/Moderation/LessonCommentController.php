<?php

namespace App\Http\Controllers\API\CL\Moderation;

use App\Achievement\Exceptions\AchievementNotExistsException;
use App\Achievement\Strategies\CreateCommentaryAchievementStrategy;
use App\Achievement\Strategies\DeleteCommentaryAchievementStrategy;
use App\Entities\Achievement;
use App\Http\Requests\CL\LessonCommentRequest;
use App\Models\CL\LessonComment;
use App\Models\CL\RateLessonComment;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LessonCommentController extends Controller
{
    /**
     * @SWG\Post(
     *     path="/api/cl-moderation/lesson/comment",
     *     summary="Create new comment.",
     *     tags={"CL (Lesson Comment)"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *     @SWG\Parameter(name="Authorization", in="header", description="Format: `Bearer <api_token>`", required=true, type="string"),
     *
     *     @SWG\Parameter(name="parent_id", description="Parent comment ID (ignore variable if it is original)",
     *          required=false, in="formData", type="integer",),
     *     @SWG\Parameter(name="lesson_id", description="Lesson ID", required=true, in="formData", type="integer",),
     *     @SWG\Parameter(name="body", description="Body of comment", required=true, in="formData", type="string",),
     *
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="comment",
     *                      @SWG\Items(ref="#/definitions/LessonComment")
     *                  ),
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
     *     @SWG\Response(response="403", description="The action is forbidden.",
     *         @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="errors", type="object",),
     *              ),
     *         ),
     *     ),
     * )
     */
    public function store(LessonCommentRequest $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $data = $request->all();
        $data['user_id'] = $user->id;

        $comment = new LessonComment();
        $comment->fill($data);
        $comment->save();

        $context = new Achievement(new CreateCommentaryAchievementStrategy($user));

        try {
            $context->run();
        } catch (\Exception $e) {
            \Log::info($e);
        }

        return response()->json(['success' => true, 'data' => ['comment' => $comment->toArray()]], 200);
    }

    /**
     * @SWG\Put(
     *     path="/api/cl-moderation/lesson/comment/{comment_id}",
     *     summary="Update comment.",
     *     tags={"CL (Lesson Comment)"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *     @SWG\Parameter(name="Authorization", in="header", description="Format: `Bearer <api_token>`", required=true, type="string"),
     *
     *     @SWG\Parameter(name="body", description="Body of comment", required=true, in="formData", type="string",),
     *
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="comment",
     *                      @SWG\Items(ref="#/definitions/LessonComment")
     *                  ),
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
     *     @SWG\Response(response="403", description="The action is forbidden.",
     *         @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="errors", type="object",),
     *              ),
     *         ),
     *     ),
     * )
     */
    public function update(LessonComment $comment, LessonCommentRequest $request)
    {
        $comment->update(['body' => $request->get('body')]);

        return response()->json(['success' => true, 'data' => ['comment' => $comment->toArray()]], 200);
    }

    /**
     * @SWG\Delete(
     *     path="/api/cl-moderation/lesson/comment/{comment_id}",
     *     summary="Delete comment.",
     *     tags={"CL (Lesson Comment)"},
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
     *     @SWG\Response(response="422", description="Validation failed",
     *         @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="errors", type="object",),
     *              ),
     *         ),
     *     ),
     *     @SWG\Response(response="403", description="The action is forbidden.",
     *         @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="errors", type="object",),
     *              ),
     *         ),
     *     ),
     * )
     */
    public function destroy(LessonComment $comment)
    {
        $response = ['success' => true, 'data' => ['message' => 'Comment deleted successfully.']];

        try {
            $comment->delete();
        } catch (\Exception $exception) {
            $response = ['success' => false, 'data' => ['message' => $exception->getMessage()]];
        }
        /** @var User $user */
        $user = Auth::user();

        $context = new Achievement(new DeleteCommentaryAchievementStrategy($user));

        try {
            $context->run();
        } catch (AchievementNotExistsException $e) {
            \Log::info($e);
        } catch (\Exception $e) {
            \Log::info($e);
        }

        return response()->json($response, 200);
    }

    /**
     * @SWG\Post(
     *     path="/api/cl-moderation/lesson/comment/{comment_id}/rate",
     *     summary="Rate comment.",
     *     tags={"CL (Lesson Comment)"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *     @SWG\Parameter(name="Authorization", in="header", description="Format: `Bearer <api_token>`", required=true, type="string"),
     *
     *     @SWG\Parameter(name="rate_value", description="Rate value (-1 or 1 or 0)", required=true, in="formData", type="integer",),
     *
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="total", type="integer"),
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
    public function rate(LessonComment $comment, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rate_value' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'data' => ['errors' => $validator->errors()]], 422);
        }

        $rateComment = RateLessonComment::where('user_id', Auth::id())->where('comment_id', $comment->id)->first();
        if (is_null($rateComment)) {
            RateLessonComment::create([
                'user_id' => Auth::id(),
                'comment_id' => $comment->id,
                'value' => intval($request->get('rate_value')),
            ]);
        } else {
            $rateComment->update([
                'value' => intval($request->get('rate_value')),
            ]);
        }

        $commentRating = intval($comment->ratedUsers->sum('value'));
        $comment->rating = $commentRating;
        $comment->update();

        return response()->json([
            'success' => true,
            'data' => [
                'total' => $commentRating,
            ]]);
    }
}
