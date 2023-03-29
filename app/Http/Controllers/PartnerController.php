<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\PartnerResource;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

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


    // public function update(Request $request, $id)
    // {
    //     $user = $request->user();
    //     $attrs = $request->validate([
    //         'name' => 'required|string'
    //     ]);

    //     $image = $this->saveImage($request->image, 'profiles');

    //     $user->update($request->only('name', 'image'));

    //     return response([
    //         'message' => 'User updated.',
    //         'user' => auth()->user()
    //     ], 200);

    // }



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
}
