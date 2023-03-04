<?php

namespace App\Http\Controllers;

use App\Models\Commande;
use App\Http\Resources\CommandeResource;
use Illuminate\Http\Request;


class CommandeController extends Controller
{
    public function index()
    {
        return CommandeResource::collection(Commande::with('commandePaniers')->get());
    }

    public function commande($commande_id)

    {
        // Récupérer une commande et ses détails de panier correspondants
        $commande = Commande::with('commandePaniers')->find($commande_id);
        return response()->json($commande);
    }

    public function getcommandefinal($commande_id)

    {
        // Récupérer une commande et ses détails de panier correspondants
        $commande = Commande::with('commandePaniers')->find($commande_id);
        // Calculer le total des prix pour chaque panier et mettre à jour le total_prix de la commande
        $total_prix = 0;
        foreach ($commande->commandePaniers as $panier) {
            $total_prix += $panier->prix * $panier->quantite;
        }
        $commande->total_prix = $total_prix;
        $commande->save();
        return response()->json($commande);
    }

    //Crud
    //get order
    public function getOrder()
    {
        return response()->json(Commande::all(), 200);
    }


    //get order by id
    public function getOrderById($id)
    {
        $commande = Commande::find($id);
        if (is_null($commande)) {
            return response()->json(['message' => 'Commande introuvable'], 404);
        }
        return response()->json(Commande::find($id), 200);
    }

    // add order
    public function addOrder(Request $request)
    {
        $commande = Commande::create($request->all());
        return response($commande, 201);
    }

    // update Order
    public function updateOrder(Request $request, $id)
    {
        $commande = Commande::find($id);
        if (is_null($commande)) {
            return response()->json(['message' => 'Commande introuvable'], 404);
        }
        $commande->update($request->only('date_cmd', 'heure_cmd', 'total_prix', 'statut'));
        return response($commande, 200);
    }

    // delete Order
    public function deleteOrder(Request $request, $id)
    {
        $commande = Commande::find($id);
        if (is_null($commande)) {
            return response()->json(['message' => 'Commande introuvable'], 404);
        }
        $commande->delete();
        return response(null, 204);
    }
}
