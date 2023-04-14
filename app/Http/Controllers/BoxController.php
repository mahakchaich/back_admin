<?php

namespace App\Http\Controllers;

use App\Models\Box;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use App\Http\Resources\PartnerResource;
use App\Models\Like;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Partner;

use function PHPSTORM_META\map;

class BoxController extends Controller
{

    public function index()
    {
        //get box
        return Box::all();
    }
    public function availableBoxs()
    {
        //get box
        return Box::where("status" ,"=","ACCEPTED")->with('likes', function ($like) {
            return $like->where('user_id', auth()->user()->id)
            ->select('user_id', 'box_id');

        })->get();
    }
    public function getfavorsBoxs()
    {
        $result = DB::table("boxs as b")
        ->join("likes as l","b.id","=","l.box_id")
        ->where('l.user_id',auth()->user()->id)->select(
            "b.id",
            "title",
            "description",
            "oldprice",
            "newprice",
            "startdate",
            "enddate",
            "quantity",
            "remaining_quantity",
            "image",
            "category",
            "partner_id",
            )
            //'title', 'description', 'oldprice', 'newprice', 'startdate', 'enddate', 'quantity', 'remaining_quantity', 'image', 'category', 'status', 'partner_id'
        ->get();
        return response([
          $result,

        ], 200);
    }

    // get all boxs
    public function index2()
    {
        return response([
            'boxs' => Box::orderBy('created_at', 'desc')->with('partner:id,name,image')->withCount('likes')
                ->with('likes', function ($like) {
                    return $like->where('user_id', auth()->user()->id)
                        ->select('id', 'user_id', 'box_id')->get();
                })
                ->get()
        ], 200);
    }
    public function indexByCategory($category)
    {
        return response([
            'boxs' => Box::where("category","=",$category)->with('likes', function ($like) {
                return $like->where('user_id', auth()->user()->id)
                ->select('user_id', 'box_id')->get();
    
            })->get()
        ], 200);
    }
 


    public function store(Request $request)
    { // Vérifie si l'utilisateur connecté est un partenaire ou un administrateur
        $user = auth()->user();
        if ($user->role_id !== 3 && $user->role_id !== 1) {
            return response()->json([
                'error' => 'Only connected partners or admins can create a box.',
                'status' => Response::HTTP_UNAUTHORIZED
            ]);
        }

        $valid = Validator::make($request->all(), [
            "title" => "required",
            "description" => "required",
            "quantity" => "required",
            "image" => "required",
            "oldprice" => "required",
            "newprice" => "required",
            "startdate" => "required",
            "enddate" => "required",
            "category" => "required",
            // "status" => "required",
        ]);

        if ($user->role_id === 1) { // if user is admin
            $valid->addRules([
                "partner_id" => "required|exists:partners,id",
            ]);
        }

        if ($valid->fails()) {
            return response()->json([
                "message" => $valid->errors(),
                "status" => 400
            ]);
        }
        $oldprice = $request->input('oldprice');
        $newprice = $request->input('newprice');
        $startdate = $request->input('startdate');
        $enddate = $request->input('enddate');
        // $partnerId = $request->input('partner_id');


        // Vérifie si l'ancien prix est superieur au nouveau prix
        if ($oldprice <= $newprice) {
            return response()->json([
                'error' => 'The old price must be higher than the new price.',
                "status" => Response::HTTP_BAD_REQUEST
            ]);
        }

        // Vérifie si la date de début est postérieure ou égale à la date actuelle
        if (strtotime($startdate) < time()) {
            response()->json([
                'error' => 'The start date must be greater than or equal to the current date.',
                'status' => Response::HTTP_BAD_REQUEST
            ]);
        }

        // Vérifie si la date de début est antérieure à la date de fin
        if (strtotime($startdate) >= strtotime($enddate)) {
            return
                response()->json([
                    'error' => 'The start date must be before the end date.',
                    'status' => Response::HTTP_BAD_REQUEST
                ]);
        }
        $box = new Box;

        // upload image section 

        if ($request->hasFile('image')) { // if file existe in the url with image type
            $completeFileName = $request->file('image')->getClientOriginalName();
            $fileNameOnly = pathinfo($completeFileName, PATHINFO_FILENAME);
            $extention = $request->file('image')->getClientOriginalExtension();
            $compPic = str_replace(' ', '_', $fileNameOnly) . '-' . rand() . '_' . time() . '.' . $extention; // create new file name 
            $path = $request->file('image')->storeAs('public/boxs_imgs', $compPic);
            $box->image = $compPic;
        }



        $box->title = $request->title;
        $box->description = $request->description;
        $box->oldprice = $request->oldprice;
        $box->newprice = $request->newprice;
        $box->startdate = $request->startdate;
        $box->enddate = $request->enddate;
        $box->quantity = $request->quantity;
        $box->remaining_quantity = $request->quantity;
        $box->category = $request->category;
        $box->status = ($user->role_id === 3) ? 'PENDING' : $request->status;
        $user = auth()->user();

        // Vérifie si l'utilisateur connecté a le rôle d'administrateur
        if ($user->role_id == 1) {
            // Si oui, vérifie si le partenaire associé à l'id existe
            if (Partner::where('id', $request->partner_id)->exists()) {
                $box->partner_id = $request->partner_id;
            } else {
                return response()->json([
                    'message' => 'Partner not found',
                    'status' => Response::HTTP_NOT_FOUND
                ]);
            }
        } else {
            // Si l'utilisateur connecté a le rôle de partenaire, utilise son propre ID
            $box->partner_id = auth()->user()->id;
        }

        $box->save();

        // Vérifie si le partenaire associé à l'id existe
        return response()->json([
            'message' => 'created successfully',
            "box_info" => $box,
            'status' => Response::HTTP_CREATED
        ]);
    }


    // get single box
    public function show($id)
    {
        return response([
            'box' => Box::where('id', $id)->withCount('likes')->get()
        ], 200);
    }


    public function update(Request $request, Box $box)
    {
        $oldprice = $request->input('oldprice');
        $newprice = $request->input('newprice');
        $startdate = $request->input('startdate');
        $enddate = $request->input('enddate');
        $partnerId = $request->input('partner_id');

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

        // Vérifie si le partenaire associé à l'id existe
        if (!DB::table('partners')->where('id', $partnerId)->exists()) {
            return response(['error' => 'Le partenaire spécifié n\'existe pas.'], Response::HTTP_BAD_REQUEST);
        }

        // Mise à jour de la boîte
        $box->update($request->all());

        // Mettre à jour la quantité restante
        $quantity = $request->input('quantity');
        $remainingQuantity = $request->input('remaining_quantity');
        if ($quantity !== null && $remainingQuantity === null) {
            $box->remaining_quantity = $quantity;
        } elseif ($quantity !== null && $remainingQuantity !== null && $remainingQuantity <= $quantity) {
            $box->remaining_quantity = $remainingQuantity;
        } else {
            return response(['error' => 'La quantité restante doit être inférieure ou égale à la quantité.'], Response::HTTP_BAD_REQUEST);
        }
        $box->save();


        $box->update($request->only('title', 'description', 'oldprice', 'newprice', 'startdate', 'enddate', 'quantity', 'remaining_quantity', 'image', 'category', 'status') + ['partner_id' => $partnerId]);
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


    public function boxdetails($id)
    {
        $box = Box::find($id);
        if (!$box) {
            return response()->json(['message' => 'Box not found'], 404);
        }

        $partner = $box->partner;
        if (!$partner) {
            return response()->json(['message' => 'Partner not found'], 404);
        }

        return new PartnerResource($partner);
    }


    //Search Box

    public function searchBox(Request $request)
    {
        $search = $request->has('search') ? $request->input('search') : "";
        $status = $request->has('status') ? $request->input('status') : "";
        //recherche des patners en fonction du paramètre:
        $boxs = Box::where('status', 'like', "%" . $status . "%")
            ->where(function ($q) use ($search) {

                $q->Where('partner_id', 'LIKE', "%{$search}%")
                    ->orWhere('title', 'LIKE', "%{$search}%")
                    ->orWhere('id', 'LIKE', "%{$search}%");
            })
            ->get();
        return response()->json($boxs);
    }

    //Filtrer boxs selon leurs status
    public function filterBoxs(Request $request)
    {
        // Récupération du paramètre de catégorie
        $status = $request->input('status');


        if (!$status) {
            return response()->json(['error' => 'Le paramètre de status est obligatoire.'], 400);
        }

        $boxs = Box::where('status', $status)->get();

        return response()->json($boxs);
    }

    public function updateBox($id, Request $request)
    {
        try {

            // Validate the request data
            $valid = Validator::make($request->all(), [
                "title" => "required",
                "description" => "required",
                "quantity" => "required",
                "image" => "required",
                "oldprice" => "required",
                "newprice" => "required",
                "startdate" => "required",
                "enddate" => "required",
                "category" => "required",
                "status" => "required",
                "partner_id" => "required|exists:partners,id",
            ]);

            if ($valid->fails()) {
                // If validation fails, return an error response
                return response()->json([
                    'errors' => $valid->errors(),
                    'status' => 400
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                "status" => 'false',
                "message" => $e->getMessage(),
                "data" => [],
                500
            ]);
        }
        // Find the resource to be updated
        $box = Box::findOrFail($id);

        if (is_null($box)) {
            return response()->json(['message' => 'partenaire introuvable'], 404);
        }

        $oldprice = $request->input('oldprice');
        $newprice = $request->input('newprice');
        $startdate = $request->input('startdate');
        $enddate = $request->input('enddate');
        $partnerId = $request->input('partner_id');

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
        // Update the resource with the new values from the request
        // Update the resource with the new values from the request
        $boxData = [
            'title' => $request->title,
            'description' => $request->description,
            'quantity' => $request->quantity,
            'oldprice' => $request->oldprice,
            'newprice' => $request->newprice,
            'startdate' => $request->startdate,
            'enddate' => $request->enddate,
            'category' => $request->category,
            'status' => $request->status,
            'partner_id' => $request->partner_id,
        ];
        if ($request->hasFile('image')) { // if file existe in the url with image type
            $completeFileName = $request->file('image')->getClientOriginalName();
            $fileNameOnly = pathinfo($completeFileName, PATHINFO_FILENAME);
            $extention = $request->file('image')->getClientOriginalExtension();
            $compPic = str_replace(' ', '_', $fileNameOnly) . '-' . rand() . '_' . time() . '.' . $extention; // create new file name 
            $path = $request->file('image')->storeAs('public/boxs_imgs', $compPic);
            // $box->image = $compPic;
            $boxData['image'] = $compPic;
        }

        Box::where('id', $id)->update($boxData);
        $box = Box::find($id);
        return response()->json([
            'message' => 'Resource updated successfully',
            'resource' => $boxData,
            'status' => 200
        ]);
    }
}
