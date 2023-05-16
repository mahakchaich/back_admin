<?php

namespace App\Http\Controllers;

use App\Models\Box;
use App\Models\Partner;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\PartnerResource;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

// use function PHPUnit\Framework\isEmpty;

class PartnerController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    //trouver tout les partnaires
    public function index()
    {
        $partners = Partner::all();
        return response()->json($partners, 200);
    }


    public function store(Request $request)
    {
        //valdiate
        $rules = [
            'name' => 'required|string',
            'email' => 'required|string|unique:users|unique:partners',
            'phone' => ['required', 'regex:/^[0-9]{8}$/'],
            'password' => 'required|string|min:6',
            'image' => 'required',
            'category' => 'required',
            'description' => 'required',
            'openingtime' => 'required',
            'closingtime' => 'required',
            'roleId' => 'exists:roles,id'
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                $validator->errors(),
                "status" => 400
            ]);
        }
        $data = $request->only('name', 'description', 'email', 'phone', 'password', 'image', 'category', 'openingtime', 'closingtime');

        // Vérifier que l'heure d'ouverture est antérieure à l'heure de fermeture
        if (strtotime($data['openingtime']) >= strtotime($data['closingtime'])) {
            return response()->json(['message' => 'L\'heure d\'ouverture doit être antérieure à l\'heure de fermeture'], 400);
        }

        $partner = new Partner;

        // upload image section 
        if ($request->hasFile('image')) { // if file existe in the url with image type
            $completeFileName = $request->file('image')->getClientOriginalName();
            $fileNameOnly = pathinfo($completeFileName, PATHINFO_FILENAME);
            $extention = $request->file('image')->getClientOriginalExtension();
            $compPic = str_replace(' ', '_', $fileNameOnly) . '-' . rand() . '_' . time() . '.' . $extention; // create new file name 
            $path = $request->file('image')->storeAs('public/partner_imgs', $compPic);
            $partner->image = $compPic;
        }
        $partner->name = $request->name;
        $partner->description = $request->description;
        $partner->email = $request->email;
        $partner->phone = $request->phone;
        $partner->password = Hash::make($request->password);
        $partner->category = $request->category;
        $partner->openingtime = $request->openingtime;
        $partner->closingtime = $request->closingtime;
        $partner->role_id = $request->roleId;
        $partner->long = $request->long;
        $partner->lat = $request->lat;
        $partner->adress = $request->adress;
        $partner->save();

        // create token for partner
        $token = $partner->createToken('Personal Access Token')->plainTextToken;

        return response()->json([
            'message' => 'Successfully registered',
            'status' => Response::HTTP_CREATED,
            'token' => $token,
        ]);
    }

    public function show($id)
    {
        $partner = Partner::partners()->find($id);
        if (is_null($partner)) {
            return response()->json(['message' => 'partner introuvable'], 404);
        }
        return response()->json(Partner::find($id), 200);
    }


    public function destroy($id)
    {
        $partner = Partner::partners()->find($id);
        if (is_null($partner)) {
            return response()->json(['message' => 'partner introuvable'], 404);
        }

        // Supprimer l'utilisateur
        $partner->delete();

        return response(null, 204);
    }


    public function showdetails($id)
    {
        $partners = Partner::with('boxs')->findOrFail($id);
        return new PartnerResource($partners);
    }

    public function showpartnerboxes()
    {
        $partner = auth()->user()->id;
        $partners = Partner::with('boxs')->findOrFail($partner);
        return new PartnerResource($partners);
    }

    //Search Partner 
    public function searchPartner(Request $request)
    {

        $search = $request->has('search') ? $request->input('search') : "";
        $category = $request->has('category') ? $request->input('category') : "";

        //recherche des patners en fonction du paramètre:
        $partners = Partner::where('category', 'like', '%' . $category . '%')
            ->where(function ($q) use ($search) {

                $q->Where('email', 'LIKE', "%{$search}%")
                    ->orWhere('phone', 'LIKE', "%{$search}%");
            })
            ->get();


        return response()->json($partners);
    }


    //Filtrer partners selon leurs catgory
    public function filterPartners(Request $request)
    {
        // Récupération du paramètre de catégorie
        $category = $request->input('category');


        if (!$category) {
            return response()->json(['error' => 'Le paramètre de catégorie est obligatoire.'], 400);
        }

        // Recherche des partenaires en fonction de la catégorie
        $partners = Partner::partners()->where('category', $category)->get();

        return response()->json($partners);
    }


    public function updatePartner($id, Request $request)
    {

        // Validate the request data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => [
                'required',
                'string',
                Rule::unique('users')->ignore($id),
                Rule::unique('partners')->ignore($id)
            ],
            'phone' => ['required', 'regex:/^[0-9]{8}$/'],
            'password' => 'required|string|min:6',
            'image' => 'required',
            'category' => 'required',
            'description' => 'required'
        ]);

        if ($validator->fails()) {
            // If validation fails, return an error response
            return response()->json([
                'errors' => $validator->errors(),
                'status' => 400
            ]);
        }

        // Find the resource to be updated
        $partner = Partner::findOrFail($id);

        if (is_null($partner)) {
            return response()->json(['message' => 'partenaire introuvable'], 404);
        }

        // Vérifier que l'heure d'ouverture est antérieure à l'heure de fermeture
        if (strtotime($request->input('openingtime')) >= strtotime($request->input('closingtime'))) {
            return response()->json(['message' => 'L\'heure d\'ouverture doit être antérieure à l\'heure de fermeture'], 400);
        }

        // Update the resource with the new values from the request

        if ($request->hasFile('image')) { // if file existe in the url with image type
            $completeFileName = $request->file('image')->getClientOriginalName();
            $fileNameOnly = pathinfo($completeFileName, PATHINFO_FILENAME);
            $extention = $request->file('image')->getClientOriginalExtension();
            $compPic = str_replace(' ', '_', $fileNameOnly) . '-' . rand() . '_' . time() . '.' . $extention; // create new file name 
            $path = $request->file('image')->storeAs('public/partner_imgs', $compPic);
            $partner->image = $compPic;
        }
        $partner->name = $request->name;
        $partner->description = $request->description;
        $partner->email = $request->email;
        $partner->phone = $request->phone;
        $partner->password = Hash::make($request->password);
        $partner->category = $request->category;
        $partner->openingtime = $request->openingtime;
        $partner->closingtime = $request->closingtime;
        $partner->save();

        return response()->json([
            'message' => 'Resource updated successfully',
            'resource' => $partner,
            'status' => 200
        ]);
    }


    public function getPartnerBoxs()
    {
        $boxs = Box::where("partner_id", "=", auth()->user()->id)->get();
        return response()->json([
            "message" => "all Partner boxs ",
            "Boxs" => $boxs,
            "satus" => 200,
        ]);
    }

    public function getPartnerBoxsAccepted()
    {
        $boxs = Box::where("partner_id", "=", auth()->user()->id)
            ->where("status", "=", "ACCEPTED")
            ->get();
        return response()->json([
            "message" => "all Partner boxs with status accepted",
            "Boxs" => $boxs,
            "status" => 200,
        ]);
    }

    public function getPartnerBoxsRejected()
    {
        $boxs = Box::where("partner_id", "=", auth()->user()->id)
            ->where("status", "=", "REJECTED")
            ->get();
        return response()->json([
            "message" => "all Partner boxs with status rejected",
            "Boxs" => $boxs,
            "status" => 200,
        ]);
    }

    public function getPartnerBoxsFinished()
    {
        $boxs = Box::where("partner_id", "=", auth()->user()->id)
            ->where("status", "=", "FINISHED")
            ->get();
        return response()->json([
            "message" => "all Partner boxs with status finished",
            "Boxs" => $boxs,
            "status" => 200,
        ]);
    }


    public function getPartnerBoxsPending()
    {
        $boxs = Box::where("partner_id", "=", auth()->user()->id)
            ->where("status", "=", "PENDING")
            ->get();
        return response()->json([
            "message" => "all Partner boxs with status pending",
            "Boxs" => $boxs,
            "status" => 200,
        ]);
    }

    public function getPartnerBoxsExpired()
    {
        $boxs = Box::where("partner_id", "=", auth()->user()->id)
            ->where("status", "=", "EXPIRED")
            ->get();
        return response()->json([
            "message" => "all Partner boxs with status expired",
            "Boxs" => $boxs,
            "status" => 200,
        ]);
    }



    public function showPartnerDetails()
    {
        $partner = Partner::find(auth()->user()->id);
        return response()->json([
            "message" => "Partner details",
            "Boxs" => $partner,
            "satus" => 200,
        ]);
    }


    public function currentPartner(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized', 'status' => 401]);
        }

        if ($user->Roles->type != 'partner') {
            return response()->json(['message' => 'Access Denied', 'status' => 401]);
        }

        $partner = Partner::find($user->id);

        return response()->json(['partner' => $partner, 'status' => 200]);
    }


    //udate partner password
    public function changePassword(Request $request)
    {
        $partner = Partner::find(auth()->user()->id);

        $validator = Validator::make($request->all(), [
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }


        // Chiffrer le nouveau mot de passe
        $password_hashed = Hash::make($request->password);

        // Mettre à jour le mot de passe dans la base de données
        $partner->password = $password_hashed;
        $partner->save();

        return response()->json(['message' => 'Mot de passe mis à jour avec succès']);
    }

    //Update Status
    public function updatePartnerStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:PENDING,ACTIVE,INACTIVE',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $partner = Partner::find($id);

        if (!$partner) {
            return response()->json(['message' => 'Partner not found'], 404);
        }

        $partner->status = $request->input('status');
        $partner->save();

        return response()->json(['message' => 'Partner status updated successfully'], 200);
    }

    //Get Partners liked
    public function getfavorsPartners()
    {
        $result = DB::table("partners as b")
            ->join("like_partners as l", "b.id", "=", "l.partner_id")
            ->where('l.user_id', auth()->user()->id)->select(
                "b.id",
                "name",
                "description",
                "email",
                "phone",
                "image",
                "category",
                "openingtime",
                "closingtime",
            )
            ->get();
        return response([
            $result,

        ], 200);
    }

    public function logout()
    {
        if (Auth::check()) {
            Auth::user()->tokens()->delete();
            return response([
                'message' => 'Logout success.'
            ], 200);
        } else {
            return response([
                'message' => 'Not authenticated.'
            ], 401);
        }
    }
    function calculateDistance($lat1, $lon1, $lat2, $lon2, $unit='km') {
        $radlat1 = pi() * $lat1 / 180;
        $radlat2 = pi() * $lat2 / 180;
        $theta = $lon1 - $lon2;
        $radtheta = pi() * $theta / 180;
        $dist = sin($radlat1) * sin($radlat2) + cos($radlat1) * cos($radlat2) * cos($radtheta);
        $dist = acos($dist);
        $dist = $dist * 180 / pi();
        $dist = $dist * 60 * 1.1515;
        if ($unit == 'km') {
            $dist = $dist * 1.609344;
        } elseif ($unit == 'm') {
            $dist = $dist * 1609.344;
        }
        return $dist;
    }
    
    public function getNearbyPartners( $lat,$long,$dist,$unity = "km")
    {

    
        $partners = Partner::select("long","lat","adress","name","created_at")->orderBy("created_at",'DESC')->get() ;
        $data = [];
            foreach($partners as $partner){
                $d = round($this->calculateDistance($lat,$long,$partner->lat,$partner->long,$unity),2);
                if ($d <= $dist ) {
                    array_push($data,$partner);
                };
            };
        return response([
            // "long" => $long,
            // "lat" => $lat,
            "partnerList" => $data,
            "distan" =>"$d : $unity",
            
        ],200);
    }



}
