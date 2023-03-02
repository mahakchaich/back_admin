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

        $panier = Panier::create($request->only('title', 'description', 'ancien_prix', 'nouveau_prix', 'date_dispo', 'quantite', 'image', 'categorie'));
        return response($panier, Response::HTTP_CREATED);
    }


    public function show(Panier $panier)
    {

        return $panier;
    }


    public function update(Request $request, Panier $panier)
    {
        $panier->update($request->only('title', 'description', 'ancien_prix', 'nouveau_prix', 'date_dispo', 'quantite', 'image', 'categorie'));
        return response($panier, Response::HTTP_CREATED);
    }

    public function destroy(Panier $panier)
    {
        $panier->delete();
        return response(null, Response::HTTP_NO_CONTENT);
    }
}
