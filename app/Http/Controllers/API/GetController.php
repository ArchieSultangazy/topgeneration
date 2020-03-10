<?php

namespace App\Http\Controllers\API;

use App\Models\AccessGroup;
use App\Models\CL\Author;
use App\Models\Job\Domain;
use App\Models\Job\Specialization;
use App\Models\Location\District;
use App\Models\Location\Locality;
use App\Models\Location\Region;
use App\Models\School;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class GetController extends Controller
{
    /**
     * @SWG\Get(
     *     path="/api/profile/specialization/available",
     *     summary="Get available specializations.",
     *     tags={"Get"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *
     *     @SWG\Parameter(name="search", description="Search word", required=true, in="formData", type="string",),
     *
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="specializations",
     *                      @SWG\Items(ref="#/definitions/Specialization")
     *                  ),
     *              ),
     *         ),
     *     ),
     * )
     */
    public function getSpecializations(Request $request)
    {
        $keyword = $request->get('search');

        return response()->json([
            'success' => true,
            'data' => [
                'specializations' => Specialization::where('name', 'LIKE', "%$keyword%")->get(),
            ]], 200);
    }

    /**
     * @SWG\Get(
     *     path="/api/profile/user-types/available",
     *     summary="Get available types of User.",
     *     tags={"Get"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="types",
     *                      @SWG\Items(ref="#/definitions/AccessGroup")
     *                  ),
     *              ),
     *         ),
     *     ),
     * )
     */
    public function getUserTypes()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'types' => AccessGroup::all(),
            ]], 200);
    }

    /**
     * @SWG\Get(
     *     path="/api/cl/authors/available",
     *     summary="Get available authors of Courses.",
     *     tags={"Get"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *
     *     @SWG\Parameter(name="search", description="Search word", required=true, in="formData", type="string",),
     *
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="authors",
     *                      @SWG\Items(ref="#/definitions/Author")
     *                  ),
     *              ),
     *         ),
     *     ),
     * )
     */
    public function getCLAuthors(Request $request)
    {
        $authors = Author::query();
        $keyword = $request->get('search');

        if (!is_null($keyword)) {
            $authors = $authors->where('firstname', 'LIKE', "%$keyword%")
                ->orWhere('lastname', 'LIKE', "%$keyword%")
                ->orWhere('middlename', 'LIKE', "%$keyword%");
        }

        return response()->json([
            'success' => true,
            'data' => [
                'authors' => $authors->get(),
            ]], 200);
    }

    /**
     * @SWG\Get(
     *     path="/api/profile/region/available",
     *     summary="Get available regions.",
     *     tags={"Get"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="regions",
     *                      @SWG\Items(ref="#/definitions/Region")
     *                  ),
     *              ),
     *         ),
     *     ),
     * )
     */
    public function getRegions()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'regions' => Region::all(),
            ]], 200);
    }

    /**
     * @SWG\Get(
     *     path="/api/profile/district/available",
     *     summary="Get available districts.",
     *     tags={"Get"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *
     *     @SWG\Parameter(name="region_id", description="ID of region", required=true, in="formData", type="integer",),
     *
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="districts",
     *                      @SWG\Items(ref="#/definitions/District")
     *                  ),
     *              ),
     *         ),
     *     ),
     * )
     */
    public function getDistricts(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'region_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'data' => ['errors' => $validator->errors()]], 422);
        } else if (intval($request->get('region_id')) >= 100) {
            return response()->json(['success' => false, 'data' => ['only_localities' => true]], 422);
        }

        $districts = District::query()->where('region_id', $request->get('region_id'))->get();

        return response()->json([
            'success' => true,
            'data' => [
                'districts' => $districts,
            ]], 200);
    }

    /**
     * @SWG\Get(
     *     path="/api/profile/locality/available",
     *     summary="Get available localities.",
     *     tags={"Get"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *
     *     @SWG\Parameter(name="region_id", description="ID of region", required=true, in="formData", type="integer",),
     *     @SWG\Parameter(name="district_id", description="ID of district (Required if region_id is city)", required=false, in="formData", type="integer",),
     *
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="localities",
     *                      @SWG\Items(ref="#/definitions/Locality")
     *                  ),
     *              ),
     *         ),
     *     ),
     * )
     */
    public function getLocalities(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'region_id' => 'required',
        ]);
        $validator->sometimes('district_id', 'required', function ($input) {
            return $input->region_id < 100;
        });
        if ($validator->fails()) {
            return response()->json(['success' => false, 'data' => ['errors' => $validator->errors()]], 422);
        }

        $localities = Locality::query()->where('region_id', $request->get('region_id'))
            ->where('district_id', $request->get('district_id'))
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'localities' => $localities,
            ]], 200);
    }

    /**
     * @SWG\Get(
     *     path="/api/profile/school/available",
     *     summary="Get available schools.",
     *     tags={"Get"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *
     *     @SWG\Parameter(name="region_id", description="ID of region (Required if region_id is city)", required=false, in="formData", type="integer",),
     *     @SWG\Parameter(name="locality_id", description="ID of locality (Required if region_id is NOT city)", required=false, in="formData", type="integer",),
     *
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="schools",
     *                      @SWG\Items(ref="#/definitions/School")
     *                  ),
     *              ),
     *         ),
     *     ),
     * )
     */
    public function getSchools(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'region_id' => 'required_without_all:locality_id',
            'locality_id' => 'required_without_all:region_id',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'data' => ['errors' => $validator->errors()]], 422);
        }

        $schools = null;
        if (!is_null($request->get('region_id'))) {
            $schools = School::query()
                ->where('region_id', $request->get('region_id'))
                ->get();
        }
        if (!is_null($request->get('locality_id'))) {
            $schools = School::query()
                ->where('locality_id', $request->get('locality_id'))
                ->get();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'schools' => $schools,
            ]], 200);
    }

    /**
     * @SWG\Get(
     *     path="/api/profile/job/domain/available",
     *     summary="Get available job domains.",
     *     tags={"Get"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="job_domains",
     *                      @SWG\Items(ref="#/definitions/JobDomain")
     *                  ),
     *              ),
     *         ),
     *     ),
     * )
     */
    public function getJobDomains()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'job_domains' => Domain::all(),
            ]], 200);
    }
}
