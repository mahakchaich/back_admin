<?php

namespace App\Http\Controllers;

use App\Models\Panier;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PanierController extends Controller
{

    public function index()
    {
        //get panier
        return Panier::all();
    }


    public function store(Request $request)
    {
        $ancien_prix = $request->input('ancien_prix');
        $nouveau_prix = $request->input('nouveau_prix');
        $date_debut = $request->input('date_debut');
        $date_fin = $request->input('date_fin');


        // Vérifie si l'ancien prix est superieur au nouveau prix
        if ($ancien_prix <= $nouveau_prix) {
            return response(['error' => 'L\'ancien prix de vente doit être supérieur au prix nouveau prix.'], Response::HTTP_BAD_REQUEST);
        }

        // Vérifie si la date de début est postérieure ou égale à la date actuelle
        if (strtotime($date_debut) < time()) {
            return response(['error' => 'La date de début doit être postérieure ou égale à la date actuelle.'], Response::HTTP_BAD_REQUEST);
        }

        // Vérifie si la date de début est antérieure à la date de fin
        if (strtotime($date_debut) >= strtotime($date_fin)) {
            return response(['error' => 'La date de début doit être antérieure à la date de fin.'], Response::HTTP_BAD_REQUEST);
        }

        $panier = Panier::create($request->only('title', 'description', 'ancien_prix', 'nouveau_prix', 'date_debut', 'date_fin', 'quantity', 'remaining_quantity', 'image', 'categorie', 'status'));
        return response($panier, Response::HTTP_CREATED);
    }


    public function show(Panier $panier)
    {

        return $panier;
    }


    public function update(Request $request, Panier $panier)
    {
        $ancien_prix = $request->input('ancien_prix');
        $nouveau_prix = $request->input('nouveau_prix');
        $date_debut = $request->input('date_debut');
        $date_fin = $request->input('date_fin');


        // Vérifie si l'ancien prix est superieur au nouveau prix
        if ($ancien_prix <= $nouveau_prix) {
            return response(['error' => 'L\'ancien prix de vente doit être supérieur au prix nouveau prix.'], Response::HTTP_BAD_REQUEST);
        }

        // Vérifie si la date de début est postérieure ou égale à la date actuelle
        if (strtotime($date_debut) < time()) {
            return response(['error' => 'La date de début doit être postérieure ou égale à la date actuelle.'], Response::HTTP_BAD_REQUEST);
        }

        // Vérifie si la date de début est antérieure à la date de fin
        if (strtotime($date_debut) >= strtotime($date_fin)) {
            return response(['error' => 'La date de début doit être antérieure à la date de fin.'], Response::HTTP_BAD_REQUEST);
        }

        $panier->update($request->only('title', 'description', 'ancien_prix', 'nouveau_prix', 'date_debut', 'date_fin', 'quantity', 'remaining_quantity', 'image', 'categorie', 'status'));
        return response($panier, Response::HTTP_CREATED);
    }

    public function destroy(Panier $panier)
    {
        // Supprimer toutes les entrées correspondantes dans la table "command_panier"
        $panier->commandPaniers()->delete();

        // Supprimer le panier
        $panier->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    //Search Panier
    public function searchPaniers(Request $request)
    {
        //récupération du paramètre de recherche:
        $search = $request->input('search');

        //vérification que le paramètre de recherche est présent:
        if (!$search) {
            return response()->json(['error' => 'Le paramètre de recherche est obligatoire.'], 400);
        }

        //recherche des utilisateurs en fonction du paramètre:
        $paniers = Panier::where('title', 'LIKE', "%{$search}%")
            ->get();

        //retourne les résultats de recherche:
        return response()->json($paniers);
    }
}
