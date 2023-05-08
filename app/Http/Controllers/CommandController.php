<?php

namespace App\Http\Controllers;

use Exception;

use App\Models\Box;
use App\Models\User;
use App\Models\Command;
use App\Models\Partner;
use App\Models\BoxCommand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\CommandResource;
use Illuminate\Support\Facades\Validator;

class CommandController extends Controller
{
    public function addOrder(Request $request)
    {
        $user = Auth::user();
        $boxId = $request->input('box_id');

        $valid = Validator::make($request->all(), [
            // "user_id" => "required|exists:users,id",
            "box_id" => "required|exists:boxs,id",
            'quantity' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) use ($request) {
                    $remainingQuantity = Box::where('id', $request->input('box_id'))
                        ->value('remaining_quantity');
                    if ($value > $remainingQuantity) {
                        $fail($attribute . ' must be less than ' . $remainingQuantity);
                    }
                },
            ],
            "status" => "required",
        ]);

        if ($valid->fails()) {
            return response()->json([
                "message" => $valid->errors(),
                "status" => 400
            ]);
        }

        // $userId = $request->input('user_id');
        $quantity = $request->input('quantity');
        $status = $request->input('status');


        // check if user status is active and box status is accepted
        // $user = User::where('id', $userId)->where('status', 'ACTIVE')->first();
        $box = Box::where('id', $boxId)->where('status', 'ACCEPTED')->first();

        // if (!$user) {
        //     return response()->json([
        //         'message' => 'User not found or inactive',
        //         'status' => '400'
        //     ]);
        // }

        if (!$box) {
            return response()->json([
                'message' => 'Box not found or not accepted',
                'status' => '400'
            ]);
        }

        // create new instances
        $box = Box::find($boxId);
        $command = new Command;
        $boxCommand = new BoxCommand();
        // Calculer le prix en fonction de la quantité et du nouveau prix
        $price = $quantity * $box->newprice;
        // Créer la commande
        // $command->user_id = $userId;
        $command->user_id = $user->role_id === 1 ? $request->input('user_id') : $user->id;
        $command->price = $price;
        $command->status = $status;
        $command->save();


        // Ajouter le panier à la commande
        $boxCommand->box_id = $boxId;
        $boxCommand->command_id = $command->id;
        $boxCommand->quantity = $quantity;
        $boxCommand->save();

        // Mettre à jour la quantité restante du panier
        $qt = $box->remaining_quantity - $quantity;
        box::where('id', $boxId)->update(['remaining_quantity' => $qt]);

        return response()->json([
            'message' => 'Commande créée avec succès',
            'status' => '200'
        ]);
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

    //Partner Orders
    public function getPartnerOrders()
    {
        $partners = Partner::where('id', auth()->user()->id)
            ->with('partnerCommands.command.user', 'partnerCommands.box')->get();
        $partnerOrders = [];

        foreach ($partners as $partner) {
            $partnerOrders[$partner->id] = $partner->partnerCommands->map(function ($partnerCommand) {
                return [
                    'command_id' => $partnerCommand->command_id,
                    'user' => $partnerCommand->command->user->name,
                    'box_name' => $partnerCommand->box->title,
                    'quantity' => $partnerCommand->quantity,
                    'created_at' => $partnerCommand->command->created_at->format('Y-m-d H:i:s')
                ];
            })->toArray();
        }

        return $partnerOrders;
    }
}
