<?php

namespace App\Http\Controllers;

use App\Http\Resources\AchievementResource;
use App\Models\Achievement;
use App\Models\UserAchievement;

/**
 * Class AchievementController
 * @package App\Http\Controllers
 */
class AchievementController extends Controller
{
    /**
     * @SWG\Get(
     *     path="/api/achievement",
     *     summary="Get all achievements",
     *     tags={"Achievement"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *     @SWG\Parameter(name="Authorization", in="header", description="Format: `Bearer <api_token>`", required=true, type="string"),
     *     @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="data", type="array",
     *                  @SWG\Items(
     *                      @SWG\Property(property="id", type="integer"),
     *                         @SWG\Property(property="ruName", type="string"),
     *                         @SWG\Property(property="kkName", type="string"),
     *                         @SWG\Property(property="enName", type="string"),
     *                         @SWG\Property(property="key", type="string"),
     *                         @SWG\Property(property="points", type="integer"),
     *                         @SWG\Property(property="createdAt", type="string"),
     *                         @SWG\Property(property="updatedAt", type="string"),
     *                         @SWG\Property(property="deletedAt", type="string"),
     *                  ),
     *              ),
     *          ),
     *     ),
     * )
     */

    /**
     * @return mixed
     */
    public function index()
    {
        $achievements = Achievement::all();

        return AchievementResource::collection($achievements);
    }

    /**
     * @SWG\Get(
     *     path="/api/achievement/{achievement}",
     *     summary="Get concrete achievements",
     *     tags={"Achievement"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *     @SWG\Parameter(name="Authorization", in="header", description="Format: `Bearer <api_token>`", required=true, type="string"),
     *     @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="data", type="object",
     *                @SWG\Property(property="id", type="integer"),
     *                @SWG\Property(property="ruName", type="string"),
     *                @SWG\Property(property="kkName", type="string"),
     *                @SWG\Property(property="enName", type="string"),
     *                @SWG\Property(property="key", type="string"),
     *                @SWG\Property(property="points", type="integer"),
     *                @SWG\Property(property="createdAt", type="string"),
     *                @SWG\Property(property="updatedAt", type="string"),
     *                @SWG\Property(property="deletedAt", type="string"),
     *              ),
     *          ),
     *     ),
     * )
     */

    /**
     * @param Achievement $achievement
     * @return AchievementResource
     */
    public function show(Achievement $achievement)
    {
        return new AchievementResource($achievement);
    }
}
