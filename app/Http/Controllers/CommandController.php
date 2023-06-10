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
use App\Http\Resources\CommandResource;
use Illuminate\Support\Facades\Validator;


class CommandController extends Controller
{

    //calcul total orders :
    public function total()
    {
        $commands = Command::all();
        $commandsCount = $commands->count();

        return response()->json([
            'commands_count' => $commandsCount
        ], 200);
    }

    public function getTotalCounts()
    {
        $pendingCount = Command::where('status', 'PENDING')->count();
        $successCount = Command::where('status', 'SUCCESS')->count();
        $cancelCount = Command::where('status', 'CANCEL')->count();

        return response()->json([
            'pending_count' => $pendingCount,
            'success_count' => $successCount,
            'cancel_count' => $cancelCount
        ], 200);
    }



    public function addOrder(Request $request)
    {
        $user = auth()->user();
        $boxId = $request->input('box_id');

        $valid = Validator::make($request->all(), [
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
            "status" => "sometimes|required",
        ]);

        if ($user->role_id === 1) { // if user is admin
            $valid->addRules([
                "user_email" => "required|exists:users,email",
            ]);
        }

        if ($valid->fails()) {
            return response()->json([
                "message" => $valid->errors(),
                "status" => 400
            ]);
        }

        $quantity = $request->input('quantity');
        $status = $request->input('status') ? $request->input('status') : 'PENDING';

        $box = Box::where('status', 'ACCEPTED')->findOrFail($boxId);

        if (!$box) {
            return response()->json([
                'message' => 'Box not found or not accepted',
                'status' => '400'
            ]);
        }

        $command = new Command;
        $boxCommand = new BoxCommand();
        $price = $quantity * $box->newprice;

        if ($user->role_id === 1) {
            // Utilisateur administrateur
            $user = User::where('email', $request->user_email)->first();
            if ($user) {
                $command->user_id =  $user->id;
            } else {
                // Utilisateur normal
                $command->user_id = $user->id;
            }
        } else {
            // Utilisateur normal
            $command->user_id = $user->id;
        }

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

        // Charger les détails de la commande
        $commande = Command::with('user', 'boxs', 'boxs.partner')->findOrFail($command->id);

        // Retourner la réponse avec les détails de la commande
        return new CommandResource($commande);
    }

    public function updateOrder(Request $request, $id)
    {
        // Trouver la commande à mettre à jour
        $command = Command::findOrFail($id);

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
        $commande = Command::with('user', 'boxs', 'boxs.partner')->findOrFail($id);
        return new CommandResource($commande);
        // Retourner la réponse avec les détails de la commande
        return new CommandResource($commande);
    }

    //Crud
    //get order
    public function getOrder()
    {
        return response()->json(Command::with('user', 'boxs.partner')->get(), 200);
    }



    //get order by id
    public function getOrderById($id)
    {
        $commande = Command::findOrFail($id);
        if (is_null($commande)) {
            return response()->json(['message' => 'Commande introuvable'], 404);
        }
        return response()->json(Command::findOrFail($id), 200);
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
        // Récupérer les paramètres de recherche
        $search = $request->input('search', '');
        $status = $request->input('status', '');

        // Rechercher les commandes en fonction des paramètres
        $commands = Command::with('user')
            ->where('status', 'like', '%' . $status . '%')
            ->when($search, function ($query) use ($search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('price', 'LIKE', "%{$search}%")
                        ->orWhereHas('user', function ($q) use ($search) {
                            $q->where('email', 'LIKE', "%{$search}%");
                        });
                });
            })
            ->get();

        // Retourner les résultats en tant que réponse JSON
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
    public function getPartnerOrders($status)
    {
        $partner = Partner::where('id', auth()->user()->id)
            ->with('partnerCommands.command.user', 'partnerCommands.box')
            ->get();

        $partnerOrders = $partner->flatMap(function ($partner) use ($status) {
            return $partner->partnerCommands->filter(function ($partnerCommand) use ($status) {
                return $partnerCommand->command->status === strtoupper($status);
            })->map(function ($partnerCommand) use ($partner) {
                return [
                    "partner_id" => $partnerCommand->box->partner_id,
                    'command_id' => $partnerCommand->command->id,
                    'price' => $partnerCommand->command->price,
                    'user_name' => $partnerCommand->command->user->name,
                    'partner_name' => $partner->name,
                    'partner_email' => $partner->email,
                    'partner_phone' => $partner->phone,
                    'user_email' => $partnerCommand->command->user->email,
                    'user_phone' => $partnerCommand->command->user->phone,
                    'box_name' => $partnerCommand->box->title,
                    'box_image' => $partnerCommand->box->image,
                    'box_description' => $partnerCommand->box->description,
                    'box_category' => $partnerCommand->box->category,
                    'box_startdate' => $partnerCommand->box->startdate,
                    'box_enddate' => $partnerCommand->box->enddate,
                    'oldprice' => $partnerCommand->box->oldprice,
                    'newprice' => $partnerCommand->box->newprice,
                    'remaining_quantity' => $partnerCommand->box->remaining_quantity,
                    'quantity' => $partnerCommand->quantity,
                    'created_at' => $partnerCommand->command->created_at->format('Y-m-d H:i:s')
                ];
            });
        })->toArray();

        return $partnerOrders;
    }


    public function getPartnerOrderCount()
    {
        $partner = Partner::where('id', auth()->user()->id)
            ->with('partnerCommands.command.user', 'partnerCommands.box')
            ->get();

        $orderCount = $partner->flatMap(function ($partner) {
            return $partner->partnerCommands;
        });

        $countByStatus = $orderCount->groupBy(function ($partnerCommand) {
            return $partnerCommand->command->status;
        })->map(function ($group) {
            return $group->count();
        });

        return $countByStatus->toArray();
    }






    //Update Status
    public function updateOrderStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:PENDING,SUCCESS,CANCEL',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $command = Command::find($id);

        if (!$command) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $command->status = $request->input('status');
        $command->save();

        return response()->json(['message' => 'Order status updated successfully'], 200);
    }



    // public function getOrdersByUser()
    // {
    //     $user = auth()->user();
    //     $commands = Command::where('user_id', $user->id)->with('user', 'boxs')->get();
    //     return CommandResource::collection($commands);
    // }

    public function getOrdersByUser($status = null)
    {
        $user = auth()->user();
        $query = Command::where('user_id', $user->id)->with('user', 'boxs', 'boxs.partner');

        if ($status) {
            $query->where('status', $status);
        }

        $commands = $query->get();

        return CommandResource::collection($commands);
    }


    public function verifQr(Request $request)
    {

        $valid = Validator::make($request->all(), [
            "command_id" => "required|exists:commands,id",
            "partner_id" => "required|exists:partners,id"
        ]);

        if ($valid->fails()) {
            return response()->json([
                "message" => $valid->errors(),
                "status" => 400
            ]);
        }
        $cmd = Command::findOrFail($request->command_id);
        if ($cmd->status == "PENDING") {
            $cmd->status = "SUCCESS";
            $cmd->save();
            return response()->json([
                'message' => 'code Verificated with success',
                'status' => '200',
                "user" => auth()->user()->id,
                "cmd" => $cmd
            ]);
        } elseif ($cmd->status == "SUCCESS") {
            return response()->json([
                'message' => 'this code already verified',
                'status' => '200',
            ]);
        } elseif ($cmd->status == "CANCEL") {
            return response()->json([
                'message' => 'THIS BOX CANCELED',
                'status' => '200',
            ]);
        }
    }
}
