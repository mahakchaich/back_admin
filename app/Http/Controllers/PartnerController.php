<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Partner;
use Illuminate\Http\Request;
use App\Http\Resources\PartnerResource;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

use function PHPUnit\Framework\isEmpty;

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

        return Partner::partners()->get();
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


        Partner::partners()->create($data);
        return response()->json([
            'message' => "successfully registered",
            "status" => Response::HTTP_CREATED
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


    public function update(Request $request, $id)
    {
        $partner = Partner::partners()->find($id);
        if (is_null($partner)) {
            return response()->json(['message' => 'partenaire introuvable'], 404);
        }

        $data = $request->only('name', 'description', 'email', 'phone', 'password', 'image', 'category', 'openingtime', 'closingtime');

        // Vérifier que l'heure d'ouverture est antérieure à l'heure de fermeture
        if (strtotime($data['openingtime']) >= strtotime($data['closingtime'])) {
            return response()->json(['message' => 'L\'heure d\'ouverture doit être antérieure à l\'heure de fermeture'], 400);
        }

        $partner->update($data);
        return response($partner, 200);
    }



    public function destroy($id)
    {
        $partner = Partner::partners()->find($id);
        if (is_null($partner)) {
            return response()->json(['message' => 'partner introuvable'], 404);
        }

        // Supprimer toutes les boxs liées à le partner
        // $partner->boxs()->delete();

        // Supprimer l'utilisateur
        $partner->delete();

        return response(null, 204);
    }


    public function showdetails($id)
    {
        $partners = Partner::with('boxs')->findOrFail($id);
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
}
