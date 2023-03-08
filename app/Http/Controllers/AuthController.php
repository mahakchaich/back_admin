<?php

namespace App\Http\Controllers;


use Cookie;
use App\Models\User;
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
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    //
    public function register(Request $request)
    {
        //valdiate
        $rules = [
            'name' => 'required|string',
            'email' => 'required|string|unique:users',
            'phone' => ['required', 'regex:/^[0-9]{8}$/'],
            'password' => 'required|string|min:6',
            'roleId' => 'exists:roles,id'
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
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
            'email' => 'required|exists:users,email',
            'password' => 'required|string'
        ]);
        if ($validator->fails()) {
            return response()->json(
                [
                    $validator->errors(),
                    "status" => 400
                ]
            );
        }
        // find user email in users table
        $user = User::where('email', $request->email)->first();
        $user_role = $user->Roles;
        $adminLogin = $request->path() === 'api/admin/login';
        if ((!$adminLogin) || ($user_role->type != "admin")) {
            return response()->json([
                "message" => "Access Denied",
                "status" => 401

            ]);
        } else if ($user && Hash::check($request->password, $user->password)) {
            // if user email found and password is correct
            $scope = $adminLogin ? 'admin' : 'user';
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
}
