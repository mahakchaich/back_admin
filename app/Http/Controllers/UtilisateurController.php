<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Utilisateur;
use Illuminate\Http\Request;

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
}
