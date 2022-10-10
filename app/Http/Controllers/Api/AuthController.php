<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CommonResource;
use App\Models\User;
use Auth;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json(new CommonResource(false, $validator->errors(), null), 422);
        }

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'is_active' => true,
            'is_admin' => false,
        ]);

        $token = $user->createToken('auth_token', ['user'])->plainTextToken;
        $user->token = $token;

        return new CommonResource(true, 'User Successfully Added!', $user);
    }

    public function login(Request $request)
    {
        try {
            if (!Auth::attempt($request->only('email', 'password'))) {
                return response()->json(new CommonResource(false, "Unauthorized", null), 401);
            }

            $user = User::where('email', $request['email'])->firstOrFail();
            if ($user->is_admin) {
                $token = $user->createToken('auth_token', ['admin'])->plainTextToken;
            } else {
                $token = $user->createToken('auth_token', ['user'])->plainTextToken;
            }
            $user->token = $token;

            return new CommonResource(true, 'You Successfully Logged In!', $user);
        } catch (\Throwable$th) {
            return response()->json(new CommonResource(false, $th->getMessage(), null), 422);
        }
    }

    public function profile()
    {
        return response()->json(['message' => 'Your Profile', 'data' => Auth::user()]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json(new CommonResource(false, $validator->errors(), null), 422);
        }

        $user->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'gender' => $request->gender,
            'phone' => $request->phone,
        ]);
        return new CommonResource(true, 'Your Profile Successfully Updated!', $user);
    }

    public function logout()
    {
        auth()->user()->tokens()->delete();
        return new CommonResource(true, 'You have been logged out', null);
    }

    public function forgot(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json(new CommonResource(false, $validator->errors(), null), 422);
        }

        $status = Password::sendResetLink(
            $request->only('email')
        );
        if ($status === Password::RESET_LINK_SENT) {
            return new CommonResource(true, 'Password reset link has been sent', $status);
        } else {
            return response()->json(new CommonResource(false, $status, null), 422);
        }

        switch ($status) {
            case Password::RESET_LINK_SENT:
                return new CommonResource(true, 'Password reset link has been sent', $status);
                break;
            case Password::RESET_THROTTLED:
                return response()->json(new CommonResource(false, "Please wait 60 seconds to request link again", null), 400);
                break;
            default:
                return response()->json(new CommonResource(false, $status, null), 422);
        }
    }

    public function reset(Request $request)
    {
        $input = $request->only('email', 'token', 'password', 'password_confirmation');
        $validator = Validator::make($input, [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:6',
        ]);
        if ($validator->fails()) {
            return response()->json(new CommonResource(false, $validator->errors(), null), 422);
        }

        $response = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => bcrypt($password),
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        switch ($response) {
            case Password::PASSWORD_RESET:
                return new CommonResource(true, 'Password reset successfully', $response);
                break;
            case Password::INVALID_TOKEN:
                return response()->json(new CommonResource(false, "Invalid token provided", null), 400);
                break;
            case Password::INVALID_USER:
                return response()->json(new CommonResource(false, "Email not found", null), 500);
                break;
            default:
                return response()->json(new CommonResource(false, $response, null), 500);
        }
    }
}
