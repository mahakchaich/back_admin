<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Mail\forgetPasswordCode;
use App\Models\verification_code;
use Illuminate\Support\Facades\Mail;
class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        //
    }


     //forget password section >>>
    // generate random code 
    function randomcode($_length)
    {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = array();
        $alphaLength = strlen($alphabet) - 1;
        for ($i = 0; $i < $_length; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); //turn the array into a string
    }

    // send random code to virif mail
    public function forgetPassWord(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|exists:users,email',
        ]);
        $user = User::where('email', $request->email)->first();
        $code = self::randomcode(4);

        if ($user) {
            $data = [
                "email" => $request->email,
                "name" => $user->name,
                "code" => $code,
                "subject" => "forget password",
            ];
            Mail::to($data["email"])->send(new forgetPasswordCode($data));
            $verifTable = new verification_code();
            $verifTable->email = $request->email;
            $verifTable->code = $code;
            $verifTable->status = "pending";
            $verifTable->save();

            return response()->json([
                'status' => 'success',
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => "email does not existe",
            ]);
        }
    }

    public function forgetPassWordReset(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|exists:users,email',
        ]);
        $user = User::where('email', $request->email)->first();
        $code = $request->code;
        // $HashedCode = verification_code::where('email', $request->email)->first();
        $HashedCode = verification_code::where(["email"=> $request->email,"status"=>"pending"])->first();
        $valide = $HashedCode["code"] === $code && $HashedCode["status"] === "pending";

        if ($valide) {
            $token = $user->createToken('Personal Access Token', ["user"])->plainTextToken;
            verification_code::where(["email"=> $request->email,"status"=>"pending"])->first()->update(["status"=>"used"]);
            return response()->json([
                'message' => "verification success",
                'token' => $token,
            ]);
        }else{
            return response()->json([
                "message"=> "invalide verification code"
            ]);
        }
       
        // return response()->json([
        //     "message" => $valide
        // ]);
    }
}
