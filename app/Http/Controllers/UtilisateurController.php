<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Utilisateur;
use Illuminate\Http\Request;

class UtilisateurController extends Controller
{

    //trouver tout les utilisateurs
    public function index()
    {
        return User::utilisateurs()->get();
    }

    //get utilisateur
    public function getUtilisateur()
    {
        return response()->json(Utilisateur::all(), 200);
    }

    //get utilisateur by Id
    public function getUtilisateurById($id)

    {
        $utilisateur = Utilisateur::find($id);
        if (is_null($utilisateur)) {
            return response()->json(['message' => 'utilisateur introuvable'], 404);
        }
        return response()->json(Utilisateur::find($id), 200);
    }

    //add utilisateur
    public function addUtilisateur(Request $request)
    {
        $utilisateur = Utilisateur::create($request->all());
        return response($utilisateur, 201);
    }

    //update utilisateur
    public function updateUtilisateur(Request $request, $id)
    {
        $utilisateur = Utilisateur::find($id);
        if (is_null($utilisateur)) {
            return response()->json(['message' => 'utilisateur introuvable'], 404);
        }
        $utilisateur->update($request->all());
        return response($utilisateur, 200);
    }

    //delete utilisateur
    public function deleteUtilisateur(Request $request, $id)
    {
        $utilisateur = Utilisateur::find($id);
        if (is_null($utilisateur)) {
            return response()->json(['message' => 'utilisateur introuvable'], 404);
        }
        $utilisateur->delete();
        return response(null, 204);
    }
}
