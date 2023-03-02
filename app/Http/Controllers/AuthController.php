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
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    //
    public function register(Request $request)
    {
        //valdiate
        $rules = [
            'name' => 'required|string',
            'email' => 'required|string|unique:users',
            'password' => 'required|string|min:6'
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        //create new user in users table
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_admin' => $request->path() === 'api/admin/login' ? 1 : 0
        ]);

        $token = $user->createToken('Personal Access Token')->plainTextToken;
        $response = ['user' => $user, 'token' => $token];
        return response()->json($response, 201);
    }


    public function login(Request $request)
    {
        // validate inputs
        $rules = [
            'email' => 'required',
            'password' => 'required|string'
        ];
        $request->validate($rules);

        // find user email in users table
        $user = User::where('email', $request->email)->first();


        $adminLogin = $request->path() === 'api/admin/login';
        if ($adminLogin && !$user->is_admin) {
            return response([
                'error' => 'Access Denied!'
            ], Response::HTTP_UNAUTHORIZED);
        }

        // if user email found and password is correct
        if ($user && Hash::check($request->password, $user->password)) {
            $scope = $adminLogin ? 'admin' : 'ambassador';
            $token = $user->createToken('Personal Access Token', [$scope])->plainTextToken;
            $response = ['user' => $user, 'token' => $token];
            $cookie = cookie('token', $token, 60 * 24); // 1 day
            return response([
                'message' => 'succes',
                'token' => $token,
            ])->withCookie($cookie);
        }

        $response = ['message' => 'Incorrect email or password'];
        return response()->json($response, 401);
    }



    public function user(Request $request)
    {
        $user = $request->user();
        return new UserResource($user);
    }


    public function logout()
    {
        $cookie = \Cookie::forget('token');
        return response([
            'message' => 'success'
        ])->withCookie($cookie);
    }

    public function updateInfo(UpdateInfoRequest $request)
    {
        $user = $request->user();
        $user->update($request->only('name', 'email'));
        return response($user, Response::HTTP_ACCEPTED);
    }

    public function updatePassword(UpdatePasswordRequest $request)
    {
        $user = $request->user();
        $user->update([
            'password' => \Hash::make($request->input('password'))
        ]);
        return response($user, Response::HTTP_ACCEPTED);
    }
}
