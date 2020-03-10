<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Notifications\PasswordResetRequest;
use App\User;
use App\Models\PasswordReset;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;

class PasswordResetController extends Controller
{
    public $successStatus = 200;

    /**
     * @SWG\Post(
     *     path="/api/password/create",
     *     summary="Create password's reset code and send it to user.",
     *     tags={"Password Reset"},
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
     * )
     */
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|numeric|digits:11',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'data' => [
                    'errors' => $validator->errors()
                ]], 422);
        }

        $user = User::where('phone', $request->get('phone'))->first();

        if (!$user) {
            return response()->json(['success' => false,
                'data' => [
                    'errors' => [
                        'user' => ['We can\'t find a user with that phone number.']
                    ]
                ]], 422);
        }

        $passwordReset = PasswordReset::updateOrCreate(
            ['phone' => $user->phone],
            [
                'phone' => $user->phone,
                'code' => $code = rand(1000, 9999),
            ]
        );

        if ($user && $passwordReset) {
            Notification::send($passwordReset, new PasswordResetRequest());
        }

        return response()->json([
            'success' => true,
            'data' => [
                'message' => 'Reset code has sent successfully.',
            ]], $this->successStatus);
    }

    /**
     * @SWG\Post(
     *     path="/api/password/verify",
     *     summary="Verify if code is valid.",
     *     tags={"Password Reset"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *
     *     @SWG\Parameter(name="phone", description="Phone number", required=true, in="formData", type="integer",),
     *     @SWG\Parameter(name="code", description="Code (which is sent to user)", required=true, in="formData", type="integer",),
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
    public function verify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|numeric|digits:11',
            'code' => 'required|numeric|digits:4',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'data' => [
                    'errors' => $validator->errors()
                ]], 422);
        }

        $passwordReset = PasswordReset::where('phone', $request->get('phone'))
            ->where('code', $request->get('code'))
            ->first();

        if (!$passwordReset) {
            return response()->json([
                'success' => false,
                'data' => [
                    'errors' => [
                        'code' => ['This password reset code is invalid.']
                    ]
                ]], 422);
        }

        if (Carbon::parse($passwordReset->created_at)->addMinutes(720)->isPast()) {
            $passwordReset->delete();

            return response()->json([
                'success' => false,
                'data' => [
                    'errors' => [
                        'code' => ['This password reset code is invalid.']
                    ]
                ]], 422);
        }

        return response()->json(['success' => true, 'data' => ['message' => 'Reset code is valid.']], $this->successStatus);
    }

    /**
     * @SWG\Post(
     *     path="/api/password/reset",
     *     summary="Reset user's password.",
     *     tags={"Password Reset"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *
     *     @SWG\Parameter(name="phone", description="Phone number", required=true, in="formData", type="string",),
     *     @SWG\Parameter(name="password", description="New password", required=true, in="formData", type="string",),
     *     @SWG\Parameter(name="c_password", description="Repeat password", required=true, in="formData", type="string",),
     *     @SWG\Parameter(name="code", description="Code (which is sent to user)", required=true, in="formData", type="string",),
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
    public function reset(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|numeric|digits:11',
            'password' => 'required|string',
            'c_password' => 'required|same:password',
            'code' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'data' => ['errors' => $validator->errors()]], 422);
        }

        $passwordReset = PasswordReset::where([
            ['code', $request->get('code')],
            ['phone', $request->get('phone')]
        ])->first();

        if (!$passwordReset) {
            return response()->json([
                'success' => false,
                'data' => [
                    'errors' => [
                        'token' => ['This password reset code is invalid.']
                    ]
                ]], 422);
        }

        $user = User::where('phone', $passwordReset->phone)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'data' => [
                    'errors' => [
                        'user' => ['We can\'t find a user with that phone number.']
                    ]
                ]], 422);
        }

        $user->password = bcrypt($request->get('password'));
        $user->save();

        $passwordReset->delete();

        return response()->json([
            'success' => true,
            'data' => [
                'message' => 'Password has been changed successfully.'
            ]], $this->successStatus);
    }
}
