<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use App\Mail\forgetPasswordCode;
use App\Models\verification_code;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;

class UtilisateurController extends Controller
{

    //trouver tout les utilisateurs
    public function getusers()
    {
        return User::utilisateurs()->get();
    }

    //get utilisateur by Id
    public function getUserById($id)

    {
        $utilisateur = User::utilisateurs()->find($id);
        if (is_null($utilisateur)) {
            return response()->json(['message' => 'utilisateur introuvable'], 404);
        }
        return response()->json(User::find($id), 200);
    }

    //add utilisateur
    public function addUser(Request $request)
    {
        $utilisateur = User::utilisateurs()->create($request->all());
        return response($utilisateur, 201);
    }

    //update utilisateur
    public function updateUser(Request $request, $id)
    {
        $utilisateur = User::utilisateurs()->find($id);
        if (is_null($utilisateur)) {
            return response()->json(['message' => 'utilisateur introuvable'], 404);
        }
        $utilisateur->update($request->all());
        return response($utilisateur, 200);
    }

    //delete utilisateur
    public function deleteUser(Request $request, $id)
    {
        $utilisateur = User::utilisateurs()->find($id);
        if (is_null($utilisateur)) {
            return response()->json(['message' => 'utilisateur introuvable'], 404);
        }
        $utilisateur->delete();
        return response(null, 204);
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
        $HashedCode = verification_code::where('email', $request->email)->first();
        $valide = $HashedCode["code"] === $code && $HashedCode["status"] === "pending";

        if ($valide) {
            $token = $user->createToken('Personal Access Token', ["user"])->plainTextToken;
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
