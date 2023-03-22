<?php

namespace App\Http\Controllers;

use App\Models\Box;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use App\Http\Resources\PartnerResource;
use Illuminate\Support\Facades\Validator;
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
        $box->status = $request->status;
        $box->partner_id = $request->partner_id;
        $box->save();

        // Vérifie si le partenaire associé à l'id existe
        return response()->json([
            'message' => 'created successfully',
            "box_info" => $box,
            'status' => Response::HTTP_CREATED
        ]);
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

    public function updateBox($id,Request $request)
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
