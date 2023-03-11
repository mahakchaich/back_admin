<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Mail\forgetPasswordCode;
use App\Models\Roles;
use App\Models\verification_code;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

use Symfony\Component\HttpFoundation\Response;


class UserController extends Controller
{
    //trouver tout les utilisateurs
    public function index()
    {

        return User::users()->get();
    }


    public function store(Request $request)
    {
        $user = User::users()->create($request->only('name', 'email', 'phone', 'password', 'status'));
        return response($user, Response::HTTP_CREATED);
    }


    public function show($id)
    {
        $user = User::users()->find($id);
        if (is_null($user)) {
            return response()->json(['message' => 'utilisateur introuvable'], 404);
        }
        return response()->json(User::find($id), 200);
    }


    public function update(Request $request, $id)
    {
        $user = User::users()->find($id);
        if (is_null($user)) {
            return response()->json(['message' => 'utilisateur introuvable'], 404);
        }
        $user->update($request->only('name', 'email', 'phone', 'status'));
        return response($user, 200);
    }


    public function destroy($id)
    {
        $user = User::users()->find($id);
        if (is_null($user)) {
            return response()->json(['message' => 'utilisateur introuvable'], 404);
        }

        // Supprimer toutes les commandes liées à l'utilisateur
        $user->commands()->delete();

        // Supprimer l'utilisateur
        $user->delete();

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
        // $HashedCode = verification_code::where('email', $request->email)->first();
        $dataBaseCode = verification_code::where(["email" => $request->email, "status" => "pending"])->first();
        $valide = $dataBaseCode["code"] === $code && $dataBaseCode["status"] === "pending";

        if ($valide) {
            $token = $user->createToken('Personal Access Token', ["user"])->plainTextToken;
            verification_code::where(["email" => $request->email, "status" => "pending"])->first()->update(["status" => "used"]);
            return response()->json([
                'message' => "verification success",
                'token' => $token,
            ]);
        } else {
            return response()->json([
                "message" => "invalide verification code"
            ]);
        }
    }

    public function addRole(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "type" => "required|unique:roles"
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => "this role already existe",
            ]);
        } else {
            $role = Roles::create([

                "type" => $request->type
            ]);
            return response()->json([
                "message" => "role added successefully",
                "status" => 200,

            ]);
        }
    }

    //Search User
    public function searchUsers(Request $request)
    {
        //récupération du paramètre de recherche:
        $search = $request->input('search');

        //vérification que le paramètre de recherche est présent:
        if (!$search) {
            return response()->json(['error' => 'Le paramètre de recherche est obligatoire.'], 400);
        }

        //recherche des utilisateurs en fonction du paramètre:
        $users = User::where('name', 'LIKE', "%{$search}%")
            ->orWhere('email', 'LIKE', "%{$search}%")
            ->orWhere('phone', 'LIKE', "%{$search}%")
            ->get();

        //retourne les résultats de recherche:
        return response()->json($users);
    }

    //Update Status
    public function updateUserStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:ACTIVE,INACTIVE',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->status = $request->input('status');
        $user->save();

        return response()->json(['message' => 'User status updated successfully'], 200);
    }
}
