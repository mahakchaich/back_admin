<?php

namespace App\Http\Controllers;

use App\Models\Box;
use Illuminate\Http\Request;
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
            "partner_id" => "required",
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
        if($request->hasFile('image')){ // if file existe in the url with image type
            $completeFileName = $request->file('image')->getClientOriginalName(); 
            $fileNameOnly = pathinfo($completeFileName,PATHINFO_FILENAME);
            $extention = $request->file('image')->getClientOriginalExtension();
            $compPic = str_replace(' ','_',$fileNameOnly).'-'. rand() . '_' . time() . '.' . $extention ; // create new file name 
            $path = $request->file('image')->storeAs('public/boxs_imgs',$compPic);
            $box->image = $compPic;
            // dd($box);
        }

        $box->title = $request->title;
        $box->description = $request->description;
        $box->oldprice = $request->oldprice;
        $box->newprice = $request->newprice;
        $box->startdate = $request->startdate;
        $box->enddate = $request->enddate;
        $box->quantity = $request->quantity;
        $box->remaining_quantity = $request->remaining_quantity;
        $box->category = $request->category;
        $box->status = $request->status;
        $box->partner_id = $request->partner_id;
        $box->save();
        //  Box::create($request->only('title', 'description', 'oldprice', 'newprice', 'startdate', 'enddate', 'quantity', 'remaining_quantity', 'image', 'category', 'status', 'partner_id'));
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

        $box->update($request->only('title', 'description', 'oldprice', 'newprice', 'startdate', 'enddate', 'quantity', 'remaining_quantity', 'image', 'category', 'status', 'partner_id'));
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

    //Search Panier
    public function searchBoxs(Request $request)
    {
        //récupération du paramètre de recherche:
        $search = $request->input('search');

        //vérification que le paramètre de recherche est présent:
        if (!$search) {
            return response()->json(['error' => 'Le paramètre de recherche est obligatoire.'], 400);
        }

        //recherche des utilisateurs en fonction du paramètre:
        $boxs = Box::where('title', 'LIKE', "%{$search}%")
            ->get();

        //retourne les résultats de recherche:
        return response()->json($boxs);
    }
}
