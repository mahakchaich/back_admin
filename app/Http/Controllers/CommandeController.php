<?php

namespace App\Http\Controllers;

use App\Http\Resources\CommandeResource;
use App\Models\Commande;
use Illuminate\Support\Facades\DB;


class CommandeController extends Controller
{
    //
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
}
