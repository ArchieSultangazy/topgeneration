<?php

namespace App\Http\Controllers\API\CL\Moderation;

use App\Http\Controllers\Controller;
use App\Http\Resources\API\CL\Moderation\TestResource;
use App\Models\CL\Lesson;
use App\Models\CL\Test;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class TestController extends Controller
{
    /**
     * @SWG\Get(
     *     path="/api/cl/moderation/tests/{lessonID}",
     *     summary="Get tests by lessonID.",
     *     tags={"Test"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="id", type="integer"),
     *                  @SWG\Property(property="lessonID", type="integer"),
     *                  @SWG\Property(property="creatorID", type="integer"),
     *                  @SWG\Property(property="createdAt", type="string"),
     *                  @SWG\Property(property="updatedAt", type="string"),
     *                  @SWG\Property(property="deletedAt", type="string"),
     *              ),
     *         ),
     *     ),
     *     @SWG\Response(response=400, description="Lesson doesnt exist",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="message", type="string")
     *              ),
     *          ),
     *     ),
     * )
     */
    public function index($lessonID)
    {
        $lesson = Lesson::find($lessonID);

        if (!$lesson) {
            return response()->json([
                'status' => true,
                'data' => [
                    'message' => 'Lesson doesnt exist',
                ],
            ], Response::HTTP_BAD_REQUEST);
        }

        $tests = $lesson->tests;

        return TestResource::collection($tests);
    }
    /**
     * @SWG\Get(
     *     path="/api/cl/moderation/tests/{testID}",
     *     summary="Get concrete test.",
     *     tags={"Test"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="id", type="integer"),
     *                  @SWG\Property(property="lessonID", type="integer"),
     *                  @SWG\Property(property="creatorID", type="integer"),
     *                  @SWG\Property(property="createdAt", type="string"),
     *                  @SWG\Property(property="updatedAt", type="string"),
     *                  @SWG\Property(property="deletedAt", type="string"),
     *              ),
     *         ),
     *     ),
     *     @SWG\Response(response=400, description="Wrong test_id. Not found",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="message", type="string")
     *              ),
     *          ),
     *     ),
     * )
     */
    public function show($testID)
    {
        $test = Test::find($testID);

        if (!$test) {
            return response()->json([
                'status' => false,
                'data' => [
                    'message' => 'Wrong test_id. Not found'
                ],
            ], Response::HTTP_BAD_REQUEST);
        }

        return new TestResource($test);
    }

    /**
     * @SWG\Post(
     *     path="/api/cl/moderation/tests",
     *     summary="Store test",
     *     tags={"Test"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *     @SWG\Parameter(name="Authorization", in="header", description="Format: `Bearer <api_token>`", required=false, type="string"),
     *     @SWG\Parameter(name="test_id",description="test_id",in="formData", required=true, type="number"),
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="id", type="integer"),
     *                  @SWG\Property(property="lessonID", type="integer"),
     *                  @SWG\Property(property="creatorID", type="integer"),
     *                  @SWG\Property(property="createdAt", type="string"),
     *                  @SWG\Property(property="updatedAt", type="string"),
     *                  @SWG\Property(property="deletedAt", type="string"),
     *              ),
     *         ),
     *     ),
     *     @SWG\Response(response=400, description="Lesson id is not set or lesson doesnt exist",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="message", type="string"),
     *              ),
     *          ),
     *     ),
     *     @SWG\Response(response=500, description="Internal server error. Cannot be saved.",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="errors", type="string"),
     *              ),
     *          ),
     *     ),
     * )
     */
    public function store(Request $request)
    {
        if (!$lessonID = $request->input('lesson_id')) {
            return response()->json([
                'status' => false,
                'data' => [
                    'message' => 'Lesson id is not set',
                ],
            ], Response::HTTP_BAD_REQUEST);
        }

        if (!$lesson = Lesson::find($lessonID)) {
            return response()->json([
                'status' => false,
                'data' => [
                    'message' => 'Lesson doesnt exist',
                ],
            ], Response::HTTP_BAD_REQUEST);
        }

        $test = new Test();
        $test->lesson_id = $lesson->id;
        $test->created_user_id = Auth::guard('api')->user()->id;

        try {
            $test->save();
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'data' => [
                    'message' => $e->getMessage(),
                ],
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new TestResource($test);
    }

    /**
     * @SWG\Patch(
     *     path="/api/cl/moderation/tests/{testID}",
     *     summary="Update test",
     *     tags={"Test"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *     @SWG\Parameter(name="Authorization", in="header", description="Format: `Bearer <api_token>`", required=false, type="string"),
     *     @SWG\Parameter(name="test_id",description="id of test",in="formData", required=true, type="number"),
     *     @SWG\Response(response=200, description="successfull operation",
     *         @SWG\Schema(type="object",
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="id", type="integer"),
     *                  @SWG\Property(property="lessonID", type="integer"),
     *                  @SWG\Property(property="creatorID", type="integer"),
     *                  @SWG\Property(property="createdAt", type="string"),
     *                  @SWG\Property(property="updatedAt", type="string"),
     *                  @SWG\Property(property="deletedAt", type="string"),
     *              ),
     *         ),
     *     ),
     *     @SWG\Response(response=400, description="Lesson id is not set or lesson doesnt exist",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="message", type="string"),
     *              ),
     *          ),
     *     ),
     *     @SWG\Response(response=500, description="Internal server error. Cannot be saved.",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="errors", type="string"),
     *              ),
     *          ),
     *     ),
     * )
     */
    public function update(Request $request, $testID)
    {
        $test = Test::find($testID);

        if (!$test) {
            return response()->json([
                'status' => false,
                'data' => [
                    'message' => 'Test doesnt exist',
                ],
            ], Response::HTTP_BAD_REQUEST);
        }
        if (!$lessonID = $request->input('lesson_id')) {
            return response()->json([
                'status' => false,
                'data' => [
                    'message' => 'lesson_id is not set',
                ],
            ], Response::HTTP_BAD_REQUEST);
        }

        if (!$lesson = Lesson::find($lessonID)) {
            return response()->json([
                'status' => false,
                'data' => [
                    'message' => 'Lesson doesnt exist',
                ],
            ], Response::HTTP_BAD_REQUEST);
        }

        $test->lesson_id = $lesson->id;

        try {
            $test->save();
        } catch (\Exception $e) {
            \Log::error($e);
            return response()->json([

            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new TestResource($test);
    }
    /**
     * @SWG\Delete(
     *     path="/api/cl/moderation/tests/{testID}",
     *     summary="Delete test",
     *     tags={"Test"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *     @SWG\Parameter(name="Authorization", in="header", description="Format: `Bearer <api_token>`", required=false, type="string"),
     *     @SWG\Response(response=200, description="successfull operation",
     *         @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="message", type="string"),
     *              ),
     *          ),
     *     ),
     *     @SWG\Response(response=400, description="Wrong test_id. Not found",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="message", type="string"),
     *              ),
     *          ),
     *     ),
     *     @SWG\Response(response=500, description="Internal server error. Cannot be saved.",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="errors", type="string"),
     *              ),
     *          ),
     *     ),
     * )
     */
    public function destroy(Test $test)
    {
        if (empty($test->toArray())) {
            return response()->json([
                'status' => false,
                'data' => [
                    'message' => 'Wrong test_id. Not found'
                ],
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $test->delete();
        } catch (\Exception $e) {
            \Log::error($e);
            return response()->json([
                'status' => false,
                'data' => [
                    'message' => $e->getMessage(),
                ],
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            'status' => true,
            'data' => [
                'message' => 'Success',
            ],
        ], Response::HTTP_OK);
    }

}
