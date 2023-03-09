<?php

namespace App\Http\Controllers;

use App\Models\Box;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class   BoxController extends Controller
{

    public function index()
    {
        //get box
        return Box::all();
    }


    public function store(Request $request)
    {
        $oldprice = $request->input('oldprice');
        $newprice = $request->input('newprice');
        $startdate = $request->input('startdate');
        $enddate = $request->input('enddate');


        // Vérifie si l'ancien prix est superieur au nouveau prix
        if ($oldprice <= $newprice) {
            return response(['error' => 'L\'ancien prix de vente doit être supérieur au prix nouveau prix.'], Response::HTTP_BAD_REQUEST);
        }

        // Vérifie si la date de début est postérieure ou égale à la date actuelle
        if (strtotime($startdate) < time()) {
            return response(['error' => 'La date de début doit être postérieure ou égale à la date actuelle.'], Response::HTTP_BAD_REQUEST);
        }

        // Vérifie si la date de début est antérieure à la date de fin
        if (strtotime($startdate) >= strtotime($enddate)) {
            return response(['error' => 'La date de début doit être antérieure à la date de fin.'], Response::HTTP_BAD_REQUEST);
        }

        $box = Box::create($request->only('title', 'description', 'oldprice', 'newprice', 'startdate', 'enddate', 'quantity', 'remaining_quantity', 'image', 'category', 'status', 'partner_id'));
        return response($box, Response::HTTP_CREATED);
    }


    public function show(Box $box)
    {

        return $box;
    }


    public function update(Request $request, Box $box)
    {
        $oldprice = $request->input('oldprice');
        $newprice = $request->input('newprice');
        $startdate = $request->input('startdate');
        $enddate = $request->input('enddate');

        // Vérifie si l'ancien prix est superieur au nouveau prix
        if ($oldprice <= $newprice) {
            return response(['error' => 'L\'ancien prix de vente doit être supérieur au prix nouveau prix.'], Response::HTTP_BAD_REQUEST);
        }

        // Vérifie si la date de début est postérieure ou égale à la date actuelle
        if (strtotime($startdate) < time()) {
            return response(['error' => 'La date de début doit être postérieure ou égale à la date actuelle.'], Response::HTTP_BAD_REQUEST);
        }

        // Vérifie si la date de début est antérieure à la date de fin
        if (strtotime($startdate) >= strtotime($enddate)) {
            return response(['error' => 'La date de début doit être antérieure à la date de fin.'], Response::HTTP_BAD_REQUEST);
        }

        $box->update($request->only('title', 'description', 'oldprice', 'newprice', 'startdate', 'enddate', 'quantity', 'remaining_quantity', 'image', 'category', 'status', 'partner_id'));
        return response($box, Response::HTTP_CREATED);
    }

    public function destroy(Box $box)
    {
        // Supprimer toutes les entrées correspondantes dans la table "command_panier"
        $box->boxsCommand()->delete();

        // Supprimer le panier
        $box->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    //Search Panier
    public function searchBoxs(Request $request)
    {
        //récupération du paramètre de recherche:
        $search = $request->input('search');

        //vérification que le paramètre de recherche est présent:
        if (!$search) {
            return response()->json(['error' => 'Le paramètre de recherche est obligatoire.'], 400);
        }

        //recherche des utilisateurs en fonction du paramètre:
        $boxs = Box::where('title', 'LIKE', "%{$search}%")
            ->get();

        //retourne les résultats de recherche:
        return response()->json($boxs);
    }
}
