<?php

namespace App\Http\Controllers;

use Exception;

use App\Models\Panier;
use App\Models\Command;
use Illuminate\Http\Request;
use App\Models\CommandPanier;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\CommandeResource;



class CommandeController extends Controller
{
    public function addOrder(Request $request)
    {
        $userId = $request->input('user_id');
        $panierId = $request->input('panier_id');
        $quantity = $request->input('quantity');
        $status = $request->input('status');

        // Récupérer les informations du panier
        $panier = Panier::find($panierId);

        // Vérifier si le panier est disponible
        if ($panier->remaining_quantity <= 0) {
            return response()->json(['message' => 'Le panier n\'est plus disponible'], 400);
        }

        // Vérifier le statut du panier
        if ($panier->status != 'ACCEPTED') {
            return response()->json(['message' => 'Le panier doit avoir un statut "ACCEPTED" pour pouvoir être commandé'], 400);
        }

        // Calculer le prix en fonction de la quantité et du nouveau prix
        $price = $quantity * $panier->nouveau_prix;

        // Vérifier la quantité disponible dans le panier
        if ($quantity > $panier->remaining_quantity) {
            return response()->json(['message' => 'Quantité demandée supérieure à la quantité restante dans le panier'], 400);
        }

        // Créer la commande
        $command = new Command;
        $command->user_id = $userId;
        $command->price = $price;
        $command->status = $status;
        $command->save();

        // Ajouter le panier à la commande
        $commandPanier = new CommandPanier;
        $commandPanier->command_id = $command->id;
        $commandPanier->panier_id = $panierId;
        $commandPanier->quantity = $quantity;
        $commandPanier->save();

        // Mettre à jour la quantité restante du panier
        $panier->remaining_quantity -= $quantity;
        $panier->save();

        return response()->json(['message' => 'Commande créée avec succès'], 201);
    }


    public function updateOrder(Request $request, $id)
    {
        // Trouver la commande à mettre à jour
        $command = Command::find($id);

        // Vérifier si la commande existe
        if (!$command) {
            return response()->json(['message' => 'Commande non trouvée'], 404);
        }

        // Mettre à jour les champs de la commande
        $command->user_id = $request->input('user_id', $command->user_id);
        $command->price = $request->input('price', $command->price);
        $command->status = $request->input('status', $command->status);
        $command->save();

        // Retourner la commande mise à jour
        return response()->json($command);
    }


    public function index()
    {
        $commandes = Command::with('user', 'paniers')->get();
        return CommandeResource::collection($commandes);
    }

    public function show($id)
    {
        $commande = Command::with('user', 'paniers')->findOrFail($id);
        return new CommandeResource($commande);
    }







    // public function index()
    // {
    //     return CommandeResource::collection(Commande::with('commandePaniers')->get());
    // }

    // public function commande($commande_id)

    // {
    //     // Récupérer une commande et ses détails de panier correspondants
    //     $commande = Commande::with('commandePaniers')->find($commande_id);
    //     return response()->json($commande);
    // }

    // public function commandedetails($commande_id)

    // {
    //     // Récupérer une commande et ses détails de panier correspondants
    //     $commande = Commande::with('commandePaniers')->find($commande_id);

    //     if (!$commande) {
    //         return response()->json(['error' => 'Commande introuvable'], 404);
    //     }
    //     // Calculer le total des prix pour chaque panier et mettre à jour le total_prix de la commande
    //     $total_prix = 0;
    //     foreach ($commande->commandePaniers as $panier) {
    //         $total_prix += $panier->prix * $panier->quantite;
    //     }
    //     $commande->total_prix = $total_prix;
    //     $commande->save();
    //     return response()->json($commande);
    // }

    //Crud
    //get order
    public function getOrder()
    {
        return response()->json(Command::all(), 200);
    }


    //get order by id
    public function getOrderById($id)
    {
        $commande = Command::find($id);
        if (is_null($commande)) {
            return response()->json(['message' => 'Commande introuvable'], 404);
        }
        return response()->json(Command::find($id), 200);
    }

    // delete Order
    public function deleteOrder(Request $request, $id)
    {
        try {
            // Supprimer les enregistrements associés dans la table "command_panier"
            DB::table('command_panier')->where('command_id', $id)->delete();
            // Supprimer la commande elle-même
            DB::table('commands')->where('id', $id)->delete();
            return response(null, 204);
        } catch (Exception $e) {
            return response()->json(['message' => 'Une erreur est survenue lors de la suppression de la commande'], 500);
        }
    }
}
