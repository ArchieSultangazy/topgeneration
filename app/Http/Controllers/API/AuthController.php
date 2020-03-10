<?php

namespace App\Http\Controllers\API;

use App\Achievement\Exceptions\AchievementExistsException;
use App\Achievement\Strategies\LoginAchievementStrategy;
use App\Achievement\Strategies\RegisterAchievementStrategy;
use App\Entities\Achievement;
use App\Http\Requests\API\UserRequest;
use App\Models\SmsVerification;
use App\Models\UserLog;
use App\User;
use App\Http\Controllers\Controller;
use App\Notifications\PhoneVerification;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * @SWG\Post(
     *     path="/api/auth/login",
     *     summary="Login.",
     *     tags={"Authorization"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *
     *     @SWG\Parameter(name="phone", description="Phone number", required=true, in="formData", type="string",),
     *     @SWG\Parameter(name="password", description="Password", required=true, in="formData", type="string",),
     *
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="token", type="string",),
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
    public function login() {
        if (Auth::attempt(['phone' => request('phone'), 'password' => request('password')])) {
            /** @var User $user */
            $user = Auth::user();

            if (is_null($user->phone_verified_at)) {
                return response()->json([
                    'success' => false,
                    'data' => [
                        'errors' => ['user' => ['Phone is not verified']]
                    ]], 422);
            }

            $token = $user->createToken('MyApp')->accessToken;
            UserLog::create(['user_id' => $user->id, 'section' => 'auth', 'action' => 'login']);

            $authAchievementStrategy = new LoginAchievementStrategy($user);
            $context = new Achievement($authAchievementStrategy);

            try {
                $context->run();
            } catch (AchievementExistsException $e) {
                \Log::info($e);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'token' => $token
                ]], 200);
        } else {
            return response()->json([
                'success' => false,
                'data' => [
                    'errors' => ['user' => ['Incorrect phone number or password']]
                ]], 422);
        }
    }

    /**
     * @SWG\Post(
     *     path="/api/auth/register",
     *     summary="Register.",
     *     tags={"Authorization"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *
     *     @SWG\Parameter(name="phone", description="Phone number", required=true, in="formData", type="string",),
     *     @SWG\Parameter(name="username", description="Nickname", in="formData", type="string",),
     *     @SWG\Parameter(name="firstname", description="Firstname", required=true, in="formData", type="string",),
     *     @SWG\Parameter(name="lastname", description="Lastname", required=true, in="formData", type="string",),
     *     @SWG\Parameter(name="middlename", description="Middlename", in="formData", type="string",),
     *
     *     @SWG\Parameter(name="region_id", description="Region ID", in="formData", type="integer",),
     *     @SWG\Parameter(name="district_id", description="District ID", in="formData", type="integer",),
     *     @SWG\Parameter(name="locality_id", description="Locality ID", in="formData", type="integer",),
     *     @SWG\Parameter(name="school_id", description="School ID", in="formData", type="integer",),
     *     @SWG\Parameter(name="class_year", description="Year of class (Ex. 10, 11)", in="formData", type="integer",),
     *     @SWG\Parameter(name="class_form", description="Form of class (Ex. А, В)", in="formData", type="string",),
     *
     *     @SWG\Parameter(name="password", description="Password", required=true, in="formData", type="string",),
     *     @SWG\Parameter(name="c_password", description="Repeat Password", required=true, in="formData", type="string",),
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
    public function register(UserRequest $request) {
        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        if (isset($input['class_form'])) {
            $input['class_form'] = mb_strtoupper($input['class_form']);
        }

        $user = User::where('phone', $input['phone'])->first();

        if (is_null($user)) {
            $user = User::create($input);

            if (!is_null($request->get('type'))) {
                $user->accessGroup()->attach($request->get('type'));
            }

            $response = $this->sendVerificationCode($input['phone']);

            UserLog::create(['user_id' => $user->id, 'section' => 'auth', 'action' => 'register']);
        } else if (is_null($user->phone_verified_at)) {
            if (!is_null($request->get('type'))) {
                $user->accessGroup()->detach();
                $user->accessGroup()->attach($request->get('type'));
            }

            $user->update($input);
            $response = $this->sendVerificationCode($input['phone']);

            UserLog::create(['user_id' => $user->id, 'section' => 'auth', 'action' => 'register']);
        } else {
            return response()->json([
                'success' => false,
                'data' => [
                    'errors' => ['phone' => ['The phone has already been taken.']]
                ]], 422);
        }

        $registerAchievementStrategy = new RegisterAchievementStrategy($user);

        try {
            $context = new Achievement($registerAchievementStrategy);
            $context->run();
        } catch (AchievementExistsException $e) {
            \Log::info($e);
        }

        return response()->json([
            'success' => $response['success'],
            'data' => [
                'message' => $response['message'],
            ]
        ], $response['status']);
    }

    /**
     * @SWG\Post(
     *     path="/api/auth/resend",
     *     summary="Resend verification code.",
     *     tags={"Authorization"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *
     *     @SWG\Parameter(name="phone", description="Phone number", required=true, in="formData", type="string",),
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
     *     @SWG\Response(response="404", description="User is not found",
     *         @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="errors", type="object",),
     *              ),
     *         ),
     *     ),
     * )
     */
    public function resend(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|numeric|digits:11',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $user = User::where('phone', $request->get('phone'))->first();

        if (is_null($user)) {
            return response()->json([
                'success' => false,
                'data' => [
                    'errors' => ['user' => ['Cannot find any user with this phone number']]
                ]], 404);
        } else if (!is_null($user->phone_verified_at)) {
            return response()->json([
                'success' => false,
                'data' => [
                    'errors' => ['user' => ['User is already verified']]
                ]], 422);
        }

        $response = $this->sendVerificationCode($user->phone);
        return response()->json([
            'success' => $response['success'],
            'data' => [
                'message' => $response['message'],
            ]
        ], $response['status']);
    }

    public function sendVerificationCode($phone)
    {
        $code = rand(1000, 9999);
        $response = ['success' => true, 'message' => ''];

        $sms = SmsVerification::create([
            'phone' => $phone,
            'code' => $code
        ]);

        try {
            Notification::send($sms, new PhoneVerification());

            $response['message'] = "Verification code has been sent successfully";
            $response['status'] = 200;
        } catch (\Exception $exception) {
            $response['success'] = false;
            $response['message'] = $exception->getMessage();
            $response['status'] = 422;
        }

        return $response;
    }

    /**
     * @SWG\Post(
     *     path="/api/auth/verify",
     *     summary="Verify phone number.",
     *     tags={"Authorization"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *
     *     @SWG\Parameter(name="phone", description="Phone number", required=true, in="formData", type="string",),
     *     @SWG\Parameter(name="code", description="Code that user enters", required=true, in="formData", type="string",),
     *
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="message", type="string",),
     *                  @SWG\Property(property="errors", type="object",),
     *              ),
     *         ),
     *     ),
     *     @SWG\Response(response="422", description="Validation failed",
     *         @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="message", type="string",),
     *                  @SWG\Property(property="errors", type="object",),
     *              ),
     *         ),
     *     ),
     * )
     */
    public function verify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|numeric|digits:11',
            'code' => 'required|numeric|digits:4',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $response = ['success' => true, 'message' => 'Phone number successfully verified', 'errors' => [], 'status' => 200];
        $user = User::where('phone', $request->get('phone'))->first();
        $smsVerification = SmsVerification::where('phone', $request->get('phone'))
            ->where('status', SmsVerification::STATUS_PENDING)
            ->latest()
            ->first();

        if ($smsVerification && $smsVerification->code == $request->get('code')) {
            $user->update(['phone_verified_at' => Carbon::now()->toDateTimeString()]);
            $smsVerification->update(['status' => SmsVerification::STATUS_VERIFIED]);
        } else {
            $response['success'] = false;
            $response['message'] = 'Failed verification';
            $response['errors'] = ['code' => ['Phone number not verified']];
            $response['status'] = 422;
        }

        return response()->json([
            'success' => $response['success'],
            'data' => [
                'message' => $response['message'],
                'errors' => $response['errors'],
            ]
        ], $response['status']);
    }
}
