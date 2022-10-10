<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CommonResource;
use App\Http\Traits\RoleTrait;
use App\Models\User;
use Illuminate\Http\Request;
use Validator;

class UserController extends Controller
{
    use RoleTrait;

    public function index()
    {
        $users = User::latest()->paginate(10);
        return new CommonResource(true, 'List of Users', $users);
    }

    public function store(Request $request)
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

        return new CommonResource(true, 'User Successfully Added!', $user);
    }

    public function show(User $user)
    {
        return new CommonResource(true, 'User Found!', $user);
    }

    public function update(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json(new CommonResource(false, $validator->errors(), null), 422);
        }

        try {
            $user->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'gender' => $request->gender,
                'phone' => $request->phone,
            ]);
            return new CommonResource(true, 'User Successfully Updated!', $user);
        } catch (\Illuminate\Database\QueryException$e) {
            $errorCode = $e->errorInfo[1];
            if ($errorCode == 1062) {
                return response()->json(new CommonResource(false, 'Email already exist', null), 422);
            }
        }
    }

    public function destroy(User $user)
    {
        $user->delete();
        return new CommonResource(true, 'User Successfully Deleted!', null);
    }

    public function assignAdmin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::whereId($request->user_id)->first();

        if ($user != null) {
            if ($user->is_admin) {
                return new CommonResource(false, 'This user already an admin', null);
            } else {
                $user->update([
                    'is_admin' => 1,
                ]);
                $user->tokens()->delete();
                $user->createToken('auth_token', ['admin']);
                return new CommonResource(true, 'User has been assigned as admin', $user);
            }
        } else {
            return response()->json(new CommonResource(false, 'User not found', null), 404);
        }
    }

    public function revokeAdmin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::whereId($request->user_id)->first();

        if ($user != null) {
            if ($user->is_admin) {
                $user->update([
                    'is_admin' => 0,
                ]);
                $user->tokens()->delete();
                $user->createToken('auth_token', ['user']);
                return new CommonResource(true, 'User has been revoke from admin', $user);
            } else {
                return new CommonResource(false, 'This user is not an admin', null);
            }
        } else {
            return response()->json(new CommonResource(false, 'User not found', null), 404);
        }
    }
}
