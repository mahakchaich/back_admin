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

        $panier = Panier::create($request->only('title', 'description', 'ancien_prix', 'nouveau_prix', 'date_debut', 'date_fin', 'quantite initial', 'quantite restante', 'image', 'categorie', 'statut'));
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

        $panier->update($request->only('title', 'description', 'ancien_prix', 'nouveau_prix', 'date_debut', 'date_fin', 'quantite initial', 'quantite restante', 'image', 'categorie', 'statut'));
        return response($panier, Response::HTTP_CREATED);
    }

    public function destroy(Panier $panier)
    {
        $panier->delete();
        return response(null, Response::HTTP_NO_CONTENT);
    }
}
