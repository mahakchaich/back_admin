<?php

namespace App\Http\Controllers;


use Cookie;
use App\Models\User;
use App\Models\Partner;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\UpdateInfoRequest;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\UpdatePasswordRequest;
use App\Models\Roles;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register', 'updatePasswordeux', 'updatePassworPartner']]);
    }

    //
    public function register(Request $request)
    {
        //valdiate
        $rules = [
            'name' => 'required|string',
            'email' => 'required|string|unique:users|unique:partners',
            'phone' => ['required', 'regex:/^[0-9]{8}$/'],
            'password' => 'required|string|min:6',
            'roleId' => 'exists:roles,id'
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                $validator->errors(),
                "status" => 400
            ]);
        }
        //create new user in users table
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role_id' => $request->roleId
        ]);

        $token = $user->createToken('Personal Access Token')->plainTextToken;
        $response = ['user' => $user, 'token' => $token];
        return response()->json($response, 201);
    }

    public function login(Request $request)
    {
        // validate inputs
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid input',
                'errors' => $validator->errors(),
                'status' => 400
            ]);
        }

        // find user by email in users or partners table
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            $user = Partner::where('email', $request->email)->first();
        }

        if (!$user || $user->status == 'INACTIVE') {
            return response()->json([
                'message' => 'Incorrect email or password',
                'status' => 401
            ]);
        }

        $user_role = $user->Roles;

        if ($request->path() === 'api/admin/login') {
            // admin login
            if ($user_role->type != "admin") {
                return response()->json([
                    "message" => "Access Denied",
                    "status" => 401
                ]);
            }

            if (Hash::check($request->password, $user->password)) {
                // if user email found and password is correct
                $scope = 'admin';
                $token = $user->createToken('token', [$scope])->plainTextToken;

                return response([
                    "status" => 200,
                    'message' => 'success',
                    'token' => $token
                ]);
            } else {
                return response()->json([
                    "message" => 'Incorrect email or password',
                    "status" => 401
                ]);
            }
        } elseif ($request->path() === 'api/user/login') {
            // user login
            if ($user_role->type != "user") {
                return response()->json([
                    "message" => "Access Denied",
                    "status" => 401
                ]);
            }

            if (Hash::check($request->password, $user->password)) {
                // if user email found and password is correct
                $scope = 'user';
                $token = $user->createToken('token', [$scope])->plainTextToken;

                return response([
                    "status" => 200,
                    'message' => 'success',
                    'token' => $token,
                    "role" => $scope
                ]);
            } else {
                return response()->json([
                    "message" => 'Incorrect email or password',
                    "status" => 401
                ]);
            }
        } elseif ($request->path() === 'api/partner/login') {
            // partner login
            if ($user_role->type != "partner") {
                return response()->json([
                    "message" => "Access Denied",
                    "status" => 401
                ]);
            }

            if ($user->status != 'ACTIVE') {
                return response()->json([
                    "message" => 'Your account is not active',
                    "status" => 401
                ]);
            }

            if (Hash::check($request->password, $user->password)) {
                // if user email found and password is correct
                $scope = 'partner';
                $token = $user->createToken('token', [$scope])->plainTextToken;

                return response([
                    "status" => 200,
                    'message' => 'success',
                    'token' => $token,
                    "role" => $scope
                ]);
            } else {
                return response()->json([
                    "message" => 'Incorrect email or password',
                    "status" => 401
                ]);
            }
        } else {
            return response()->json([
                "message" => "Invalid request path",
                "status" => 400
            ]);
        }
    }

    public function logout()
    {
        if (Auth::check()) {
            Auth::user()->tokens()->delete();
            // Auth::logout();
            return response([
                'message' => 'Logout success.'
            ], 200);
        } else {
            return response([
                'message' => 'Not authenticated.'
            ], 401);
        }
    }



    public function user(Request $request)
    {
        $user = $request->user();
        return new UserResource($user);
    }

    public function updateInfo(UpdateInfoRequest $request)
    {
        $user = $request->user();
        $user->update($request->only('name', 'email', 'phone'));
        return response($user, Response::HTTP_ACCEPTED);
    }


    public function updatePassword(UpdatePasswordRequest $request)
    {
        $user = $request->user();
        $user->update([
            'password' => Hash::make($request->input('password'))
        ]);
        return response($user, Response::HTTP_ACCEPTED);
    }

    // // change password
 
    public function updatePasswordeux(Request $request)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            // If validation fails, return an error response
            return response()->json([
                'errors' => $validator->errors(),
                'status' => 400
            ]);
        }

        // Find the user with the specified email
        $user = User::where('email', $request->email)->first();

        if (is_null($user)) {
            // If no user is found, try to find a partner
            $partner = Partner::where('email', $request->email)->first();

            if (is_null($partner)) {
                // If no user or partner is found, return an error response
                return response()->json(['message' => 'User or Partner not found'], 404);
            }
            // Update the partner's password with the new value from the request
            $partner->password = bcrypt($request->password);
            $partner->save();
        } else {
            // Update the user's password with the new value from the request
            $user->password = bcrypt($request->password);
            $user->save();
        }

        return response()->json([
            'message' => 'Password updated successfully',
            'status' => 200
        ]);
    }
}
