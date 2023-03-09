<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PartnerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    //trouver tout les partnaires
    public function index()
    {

        return Partner::partners()->get();
    }

    public function store(Request $request)
    {
        $partner = Partner::partners()->create($request->only('name', 'description', 'email', 'phone', 'password', 'image', 'category', 'openingtime', 'closingtime'));
        return response($partner, Response::HTTP_CREATED);
    }


    public function show($id)
    {
        $partner = Partner::partners()->find($id);
        if (is_null($partner)) {
            return response()->json(['message' => 'partner introuvable'], 404);
        }
        return response()->json(Partner::find($id), 200);
    }


    public function update(Request $request, $id)
    {
        $partner = Partner::partners()->find($id);
        if (is_null($partner)) {
            return response()->json(['message' => 'paetner introuvable'], 404);
        }
        $partner->update($request->only('name', 'description', 'email', 'phone', 'password', 'image', 'category', 'openingtime', 'closingtime'));
        return response($partner, 200);
    }


    public function destroy($id)
    {
        $partner = Partner::partners()->find($id);
        if (is_null($partner)) {
            return response()->json(['message' => 'partner introuvable'], 404);
        }

        // Supprimer toutes les boxs liées à le partner
        // $partner->boxs()->delete();

        // Supprimer l'utilisateur
        $partner->delete();

        return response(null, 204);
    }
}
