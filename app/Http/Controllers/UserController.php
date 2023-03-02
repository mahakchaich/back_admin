<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    //trouver tout les utilisateurs
    public function index()
    {

        return User::utilisateurs()->get();
    }


    public function store(Request $request)
    {
        $user = User::utilisateurs()->create($request->only('name', 'email', 'phone', 'password'));
        return response($user, Response::HTTP_CREATED);
    }


    public function show(User $user)
    {
        return $user = User::utilisateurs();
    }


    public function update(Request $request, $id)
    {
        $user = User::utilisateurs()->find($id);
        if (is_null($user)) {
            return response()->json(['message' => 'utilisateur introuvable'], 404);
        }
        $user->update($request->only('name', 'email', 'phone', 'password'));
        return response($user, 200);
    }


    public function destroy($id)
    {
        $user = User::utilisateurs()->find($id);
        if (is_null($user)) {
            return response()->json(['message' => 'utilisateur introuvable'], 404);
        }
        $user->delete();
        return response(null, 204);
    }
}
