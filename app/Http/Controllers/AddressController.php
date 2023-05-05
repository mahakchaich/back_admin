<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Partner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class AddressController extends Controller
{

    public function index()
    {
        //
    }

    public function store(Request $request)
    {
        $valid = Validator::make($request->all(), [
            "lat" => "required",
            "lng" => "required",
            "position" => "required",
        ]);

        if ($valid->fails()) {
            return response()->json([
                "message" => $valid->errors(),
                "status" => 400
            ]);
        }

        $address = new Address();

        $address->lat = $request->lat;
        $address->lng = $request->lng;
        $address->position = $request->position;


        $user = auth()->user();

        // Vérifie si l'utilisateur connecté a le rôle d'administrateur
        if ($user->role_id == 1) {
            // Si oui, vérifie si le partenaire associé à l'id existe
            if (Partner::where('id', $request->partner_id)->exists()) {
                $address->partner_id = $request->partner_id;
            } else {
                return response()->json([
                    'message' => 'Partner not found',
                    'status' => Response::HTTP_NOT_FOUND
                ]);
            }
        } else {
            // Si l'utilisateur connecté a le rôle de partenaire, utilise son propre ID
            $address->partner_id = auth()->user()->id;
        }

        $address->save();

        // Vérifie si le partenaire associé à l'id existe
        return response()->json([
            'message' => 'created successfully',
            "address_info" => $address,
            'status' => Response::HTTP_CREATED
        ]);
    }


    public function show(Address $address)
    {
        //
    }


    public function update(Request $request, Address $address)
    {
        //
    }


    public function destroy(Address $address)
    {
        //
    }
}
