<?php

namespace App\Http\Controllers;

use Exception;

use App\Models\Box;
use App\Models\Command;
use App\Models\BoxCommand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\CommandResource;
use Illuminate\Support\Facades\Validator;

class CommandController extends Controller
{
    public function addOrder(Request $request)
    {
        $valid = Validator::make($request->all(), [
            "user_id" => "required|exists:users,id",
            "box_id" => "required|exists:boxs,id",
            "quantity" => "required|integer|lt:{remaining_quantity}",
            "status" => "required",
        ]);
        if ($valid->fails()) {
            return response()->json([
                "message" => $valid->errors(),
                "status" => 400
            ]);
        }
        $userId = $request->input('user_id');
        $boxId = $request->input('box_id');
        $quantity = $request->input('quantity');
        $status = $request->input('status');

        $box = Box::find($boxId);

        // Calculer le prix en fonction de la quantité et du nouveau prix
        $price = $quantity * $box->newprice;

        // Créer la commande
        $command = new Command;
        $command->user_id = $userId;
        $command->price = $price;
        $command->status = $status;
        $command->save();

        // Ajouter le panier à la commande
        $boxCommand = new BoxCommand();
        $boxCommand->box_id = $boxId;
        $boxCommand->command_id = $command->id;
        $boxCommand->quantity = $quantity;
        $boxCommand->save();

        // Mettre à jour la quantité restante du panier
        $box->remaining_quantity -= $quantity;
        $box->save();

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
        $commands = Command::with('user', 'boxs')->get();
        return CommandResource::collection($commands);
    }

    public function show($id)
    {
        $commande = Command::with('user', 'boxs')->findOrFail($id);
        return new CommandResource($commande);
    }

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
            DB::table('box_command')->where('command_id', $id)->delete();
            // Supprimer la commande elle-même
            DB::table('commands')->where('id', $id)->delete();
            return response(null, 204);
        } catch (Exception $e) {
            return response()->json(['message' => 'Une erreur est survenue lors de la suppression de la commande'], 500);
        }
    }


    //Search User
    public function searchOrder(Request $request)
    {

        $search = $request->has('search') ? $request->input('search') : "";
        $status = $request->has('status') ? $request->input('status') : "";

        //recherche des patners en fonction du paramètre:
        $commands = Command::Where('status', 'like', '%' . $status . '%')
            ->where(function ($q) use ($search) {
                $q->Where('id', 'LIKE', "%{$search}%")
                    ->orWhere('user_id', 'LIKE', "%{$search}%");
            })
            ->get();


        return response()->json($commands);
    }



    //Filtrer commands selon leurs status
    public function filterOrders(Request $request)
    {
        // Récupération du paramètre de catégorie
        $status = $request->input('status');


        if (!$status) {
            return response()->json(['error' => 'Le paramètre de status est obligatoire.'], 400);
        }

        $orders = Command::where('status', $status)->get();

        return response()->json($orders);
    }
}
