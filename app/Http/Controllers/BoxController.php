<?php

namespace App\Http\Controllers;

use App\Models\Box;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

use Illuminate\Support\Facades\DB;
use App\Http\Resources\PartnerResource;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Partner;
use Illuminate\Support\Facades\Auth;

use function PHPSTORM_META\map;

class BoxController extends Controller
{

    public function index()
    {
        //get box
        return Box::all();
    }

    //calcul total boxes :
    public function total()
    {
        $boxes = Box::all();
        $boxesCount = $boxes->count();

        return response()->json([
            'boxes_count' => $boxesCount
        ], 200);
    }

    public function getTotalBoxCounts()
    {
        $pendingCount = Box::where('status', 'PENDING')->count();
        $acceptedCount = Box::where('status', 'ACCEPTED')->count();
        $rejectedCount = Box::where('status', 'REJECTED')->count();
        $finishedCount = Box::where('status', 'FINISHED')->count();
        $expiredCount = Box::where('status', 'EXPIRED')->count();

        return response()->json([
            'pending_count' => $pendingCount,
            'accepted_count' => $acceptedCount,
            'rejected_count' => $rejectedCount,
            'finished_count' =>  $finishedCount,
            'expired_count' => $expiredCount,
        ], 200);
    }

    public function getTotalBoxCountsstat()
    {
        $partnerId = auth()->user()->id;

        $pendingCount = Box::where('partner_id', $partnerId)->where('status', 'PENDING')->count();
        $acceptedCount = Box::where('partner_id', $partnerId)->where('status', 'ACCEPTED')->count();
        $rejectedCount = Box::where('partner_id', $partnerId)->where('status', 'REJECTED')->count();
        $finishedCount = Box::where('partner_id', $partnerId)->where('status', 'FINISHED')->count();
        $expiredCount = Box::where('partner_id', $partnerId)->where('status', 'EXPIRED')->count();

        return response()->json([
            'PENDING' => $pendingCount,
            'ACCEPTED' => $acceptedCount,
            'REJECTED' => $rejectedCount,
            'FINISHED' =>  $finishedCount,
            'EXPIRED' => $expiredCount,
        ], 200);
    }




    public function availableBoxs()
    {
        //get box
        return Box::where("status", "=", "ACCEPTED")->with('likes', function ($like) {
            return $like->where('user_id', auth()->user()->id)
                ->select('user_id', 'box_id')->count();
        })->get();
    }
    public function getfavorsBoxs()
    {
        $result = DB::table("boxs as b")
            ->join("likes as l", "b.id", "=", "l.box_id")
            ->where('l.user_id', auth()->user()->id)->select(
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

    //filtre
    public function indexByCategory($category)
    {
        return response([
            'boxs' => Box::where("category", "=", $category)->where('boxs.status', 'ACCEPTED')->with('likes', function ($like) {
                return $like->where('user_id', auth()->user()->id)
                    ->select('user_id', 'box_id')->get();
            })->get()
        ], 200);
    }

    public function indexByPartnerCategory($category)
    {
        return response([
            'boxs' => Box::join('partners', 'partners.id', '=', 'boxs.partner_id')
                ->where('partners.category', $category)
                ->where('boxs.status', 'ACCEPTED')
                ->with('likes', function ($like) {
                    return $like->where('user_id', auth()->user()->id)
                        ->select('user_id', 'box_id')->get();
                })->get()
        ], 200);
    }


    public function filterprice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'min' => 'required|numeric',
            'max' => 'required|numeric|gte:min',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->first(),
                'status' => Response::HTTP_BAD_REQUEST
            ]);
        }

        $min = $request->input('min');
        $max = $request->input('max');

        $boxQuery = Box::where('newprice', '>=', $min)
            ->where('newprice', '<=', $max)
            ->where('status', '=', 'ACCEPTED')
            ->with('partner:id,name,image')
            ->with('likes', function ($like) {
                return $like->where('user_id', auth()->user()->id)
                    ->select('id', 'user_id', 'box_id');
            });

        $boxs = $boxQuery->get();

        return response([
            'boxs' => $boxs
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
            "quantity" => "required|gte:1",
            "image" => "required",
            "oldprice" => "required",
            "newprice" => "required",
            "startdate" => "required",
            "enddate" => "required",
            "category" => "required",
        ]);
        if ($user->role_id === 1) { // if user is admin
            $valid->addRules([
                "partner_email" => "required|exists:partners,email",
            ]);
        }



        if ($valid->fails()) {
            // Vérifier si un des champs est vide
            $isEmptyField = in_array('', $request->all());

            return response()->json([
                "message" => $valid->errors(),
                "status" => $isEmptyField ? 401 : 400
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
                "status" => 422
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
                    'status' => 403
                ]);
        }
        // Vérifie si la différence entre la date de début et la date de fin est supérieure à deux jours
        $diffInSeconds = strtotime($enddate) - strtotime($startdate);
        $diffInDays = floor($diffInSeconds / (60 * 60 * 24));
        if ($diffInDays > 2) {
            return response()->json([
                'error' => 'The difference between start date and end date must not exceed two days.',
                'status' => 404
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
            // Si oui, vérifie si le partenaire associé à l'email existe
            $partner = Partner::where('email', $request->partner_email)->first();
            if ($partner) {
                $box->partner_id = $partner->id;
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
            'status' => 200
        ]);
    }

    // get single box
    public function show($id)
    {
        return response([
            'box' => Box::where('id', $id)->withCount('likes')->get()
        ], 200);
    }
    // get single box
    public function showPartner($id)
    {
        return response([
            'partner' => Partner::where('id', $id)->with('likes')->get()
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
    public function searchBoxs(Request $request)
    {
        $search = $request->has('search') ? $request->input('search') : "";
        $status = $request->has('status') ? $request->input('status') : "";
        //recherche des patners en fonction du paramètre:
        $boxs = Box::where('status', 'like', "%" . $status . "%")
            ->where(function ($q) use ($search) {

                $q->Where('newprice', 'LIKE', "%{$search}%")
                    ->orWhere('title', 'LIKE', "%{$search}%")
                    ->orWhere('category', 'LIKE', "%{$search}%");
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

    //Update Status
    public function updateBoxStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:PENDING,ACCEPTED,REJECTED,FINISHED,EXPIRED',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $box = Box::find($id);

        if (!$box) {
            return response()->json(['message' => 'Box not found'], 404);
        }

        $box->status = $request->input('status');
        $box->save();

        return response()->json(['message' => 'Box status updated successfully'], 200);
    }

    public function updateBoxDetails(Request $request,  $id)
    {
        $valid = Validator::make($request->all(), [
            "title" => "required",
            "description" => "required",
            "quantity" => "required|gte:1",
            "oldprice" => "required",
            "newprice" => "required",
            "startdate" => "required",
            "enddate" => "required",
            "category" => "required",
        ]);
        $oldprice = $request->input('oldprice');
        $newprice = $request->input('newprice');
        $startdate = $request->input('startdate');
        $enddate = $request->input('enddate');
        $quantity = $request->input('quantity');
        $remainingQuantity = $request->input('remaining_quantity');
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

        $partner = Auth::user();

        $box = $partner->boxs()->find($id);

        // Mise à jour de la boîte
        // $box->update($request->all());

        // Mettre à jour la quantité restante

        if ($quantity !== null && $remainingQuantity === null) {
            $box->remaining_quantity = $quantity;
        } elseif ($quantity !== null && $remainingQuantity !== null && $remainingQuantity <= $quantity) {
            $box->remaining_quantity = $remainingQuantity;
        } else {
            return response(['error' => 'La quantité restante doit être inférieure ou égale à la quantité.'], Response::HTTP_BAD_REQUEST);
        }

        $box->update($request->only('title', 'description', 'oldprice', 'newprice', 'startdate', 'enddate', 'quantity', 'remaining_quantity', 'category'));

        return response()->json([
            // 'partner' => $partner,
            'box' => $box,
            'message' => 'Box status updated successfully'
        ], 200);
    }



    public function updateBoxImage(Request $request, $id)
    { // Vérifie si l'utilisateur connecté est un partenaire ou un administrateur
        $user = auth()->user();
        if ($user->role_id !== 3 && $user->role_id !== 1) {
            return response()->json([
                'error' => 'Only connected partners or admins can create a box.',
                'status' => Response::HTTP_UNAUTHORIZED
            ]);
        }

        $valid = Validator::make($request->all(), [
            "image" => "required",
        ]);

        if ($valid->fails()) {
            // Vérifier si un des champs est vide
            $isEmptyField = in_array('', $request->all());
            return response()->json([
                "message" => $valid->errors(),
                "status" => $isEmptyField ? 401 : 400
            ]);
        }
        $partner = Auth::user();

        $box = $partner->boxs()->find($id);

        // upload image section 
        if ($request->hasFile('image')) { // if file existe in the url with image type
            $completeFileName = $request->file('image')->getClientOriginalName();
            $fileNameOnly = pathinfo($completeFileName, PATHINFO_FILENAME);
            $extention = $request->file('image')->getClientOriginalExtension();
            $compPic = str_replace(' ', '_', $fileNameOnly) . '-' . rand() . '_' . time() . '.' . $extention; // create new file name 
            $path = $request->file('image')->storeAs('public/boxs_imgs', $compPic);
            $box->image = $compPic;
        }
        $box->save();
        // Vérifie si le partenaire associé à l'id existe
        return response()->json([
            'message' => 'created successfully',
            "box_info" => $box,
            'status' => 200
        ]);
    }
    public function recommandedBoxs(Request $request,$name)
    { 
        $status = 'ACCEPTED';
        $response = Http::get('http://127.0.0.1:5000/api/data/'.$name);
        $data = $response->json();
        // $boxs = Box::where("partner_id",$data)->get();
        // $boxs = Partner::whereIn("name",$data['recommended_partner_names'])->boxs()->get();
    // $boxs = Partner::whereIn("name", $data['recommended_partner_names'])
    //     ->with(['boxs' => function ($query) use ($status) {
    //         $query->where('status', $status);
    //     }])
    //     ->get();
    $boxs = Partner::whereIn("name", $data['recommended_partner_names'])
    ->whereHas('boxs', function ($query) use ($status) {
        $query->where('status', $status);
    })
    ->with(['boxs' => function ($query) use ($status) {
        $query->where('status', $status);
    }])
    ->get()
    ->pluck('boxs')
    ->flatten();

              return response()->json([
            'message' => 'created successfully',
            // 'data' => $data,
            'boxs' => $boxs, 
            'status' => 200
        ]);
    }
}
