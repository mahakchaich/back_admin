<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Roles;
use App\Models\BoxCommand;
use App\Models\Command;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Mail\forgetPasswordCode;
use App\Models\verification_code;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

use App\Http\Resources\CommandResource;
use App\Models\Box;
use App\Models\Partner;
use App\Models\rating;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;


class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
        return response()->json($users, 200);
    }

    public function total()
    {
        $users = User::where('role_id', 2)->get();
        $usersCount = $users->count();

        return response()->json([
            'users_count' => $usersCount
        ], 200);
    }

    public function getTotalUserCounts()
    {
        $activeCount = User::where('status', 'ACTIVE')
            ->where('role_id', 2)
            ->count();

        $inactiveCount = User::where('status', 'INACTIVE')
            ->where('role_id', 2)
            ->count();

        return response()->json([
            'active_count' => $activeCount,
            'inactive_count' => $inactiveCount,
        ], 200);
    }



    public function store(Request $request)
    {
        //valdiate
        $rules = [
            'name' => 'required|string',
            'email' => 'required|string|unique:users|unique:partners|regex:/^[A-Za-z0-9._%-]+@[A-Za-z0-9._%-]+\\.[a-z]{2,3}$/',
            'phone' => ['required', 'regex:/^[0-9]{8}$/'],
            'password' => 'required|string|min:6',
            'status' => 'required',
            'roleId' => 'exists:roles,id'
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                $validator->errors(),
                "status" => 400
            ]);
        }
        $data = $request->only('name', 'email', 'phone', 'password', 'status');


        $user = new User();


        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->password = Hash::make($request->password);
        $user->status = $request->status;
        $user->role_id = $request->roleId;
        $user->save();

        return response()->json([
            'message' => "successfully registered",
            "status" => Response::HTTP_CREATED
        ]);
    }



    public function show($id)
    {
        $user = User::users()->find($id);
        if (is_null($user)) {
            return response()->json(['message' => 'utilisateur introuvable'], 404);
        }
        return response()->json(User::find($id), 200);
    }


    public function update(Request $request, $id)
    {
        //valdiate
        // $rules = [];
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => [
                'required',
                'string',
                Rule::unique('users')->ignore($id),
                Rule::unique('partners')->ignore($id),

                'regex:/^[A-Za-z0-9._%-]+@[A-Za-z0-9._%-]+\\.[a-z]{2,3}$/'
            ],
            'phone' => ['required', 'regex:/^[0-9]{8}$/'],
        ]);
        if ($validator->fails()) {
            return response()->json([
                $validator->errors(),
                "status" => 400
            ]);
        }
        $user = User::users()->find($id);
        if (is_null($user)) {
            return response()->json(
                [
                    'message' => 'utilisateur introuvable',
                    "status" => "404"
                ]
            );
        }
        $user->update($request->only('name', 'email', 'phone', 'status'));
        return response()->json([
            "message" => "Updated Successefully",
            "status" => 200,
        ]);
    }


    public function destroy($id)
    {
        $user = User::users()->find($id);
        if (is_null($user)) {
            return response()->json(['message' => 'utilisateur introuvable'], 404);
        }

        // Supprimer toutes les commandes liées à l'utilisateur
        $user->commands()->delete();

        // Supprimer l'utilisateur
        $user->delete();

        return response(null, 204);
    }


    //forget password section >>>
    // generate random code 
    function randomcode($_length)
    {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = array();
        $alphaLength = strlen($alphabet) - 1;
        for ($i = 0; $i < $_length; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); //turn the array into a string
    }

    // send random code to virif mail
    public function forgetPassWord(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email' => [
                'required',
                'email',
                function ($attribute, $value, $fail) {
                    if (!User::where('email', $value)->exists() && !Partner::where('email', $value)->exists()) {
                        $fail('The selected email is invalid.');
                    }
                },
            ],
        ]);


        if ($validator->fails()) {
            return response()->json([
                $validator->errors(),
                "status" => 400
            ]);
        }

        $user = User::where('email', $request->email)->first();
        $partner = Partner::where('email', $request->email)->first();
        $code = self::randomcode(4);
        // make old codes expired
        $oldCodes = verification_code::where(["email" => $request->email, "status" => "pending"])->update(["status" => "expired"]);
        // $oldCodes->update("status","expired");
        // $dataBaseCode = verification_code::where(["email" => $request->email, "status" => "pending","code"=>$code])->orderBy('created_at', 'desc')->first();
        // 
        if ($user) {
            $data = [
                "email" => $request->email,
                "name" => $user->name,
                "code" => $code,
                "subject" => "forget password",
            ];
            Mail::to($data["email"])->send(new forgetPasswordCode($data));
            $verifTable = new verification_code();
            $verifTable->email = $request->email;
            $verifTable->code = $code;
            $verifTable->status = "pending";
            $verifTable->save();

            return response()->json([
                'status' => 'success',
            ]);
        } else if ($partner) {
            $data = [
                "email" => $request->email,
                "name" => $partner->name,
                "code" => $code,
                "subject" => "forget password",
            ];
            Mail::to($data["email"])->send(new forgetPasswordCode($data));
            $verifTable = new verification_code();
            $verifTable->email = $request->email;
            $verifTable->code = $code;
            $verifTable->status = "pending";
            $verifTable->save();

            return response()->json([
                'status' => 'success',
            ]);
        } else {
            return response()->json([
                'status' => 'sucess',
                'message' => "partner",
            ]);
        }
    }

    public function verifCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => [
                'required',
                'email',
                function ($attribute, $value, $fail) {
                    if (!User::where('email', $value)->exists() && !Partner::where('email', $value)->exists()) {
                        $fail('The selected email is invalid.');
                    }
                },
            ],
            'code' => [
                "required",
                "string",
                "min:4",
                "max:4",
                "exists:verification_codes,code"
            ]
        ]);


        if ($validator->fails()) {
            return response()->json([
                $validator->errors(),
                "status" => 400
            ]);
        }
        $code = $request->code;
        $dataBaseCode = verification_code::where(["email" => $request->email, "status" => "pending", "code" => $code])->orderBy('created_at', 'desc')->first();

        if ($dataBaseCode) {
            $dataBaseCode->status = "used";
            $dataBaseCode->save();
            return response()->json([
                'message' => "verification success",
                "status" => 200,
                "code" => $dataBaseCode
            ]);
        } else {
            return response()->json([
                "message" => "invalide verification code",
                "status" =>  406 // not acceptable == 406
            ]);
        }
    }

    public function addRole(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "type" => "required|unique:roles"
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => "this role already existe",
            ]);
        } else {
            $role = Roles::create([

                "type" => $request->type
            ]);
            return response()->json([
                "message" => "role added successefully",
                "status" => 200,

            ]);
        }
    }

    //Search User
    public function searchUsers(Request $request)
    {
        //récupération du paramètre de recherche:
        $search = $request->input('search');

        //vérification que le paramètre de recherche est présent:
        if (!$search) {
            return response()->json(['error' => 'Le paramètre de recherche est obligatoire.'], 400);
        }

        //recherche des utilisateurs en fonction du paramètre:
        $users = User::where('name', 'LIKE', "%{$search}%")
            ->orWhere('email', 'LIKE', "%{$search}%")
            ->orWhere('phone', 'LIKE', "%{$search}%")
            ->get();

        //retourne les résultats de recherche:
        return response()->json($users);
    }

    //Update Status
    public function updateUserStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:ACTIVE,INACTIVE',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->status = $request->input('status');
        $user->save();

        return response()->json(['message' => 'User status updated successfully'], 200);
    }

    // update user password
    public function updateUserPassword(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found', 'status' => 404], 404);
        }

        $user->password = Hash::make($request->input('password'));
        $user->update();

        return response()->json(
            [
                'message' => 'User password updated successfully',
                'status' => 200
            ],
            200
        );
    }

    //afficher la liste des commands de chaque user
    public function showuser($userId)
    {
        $user = User::findOrFail($userId);
        $commands = $user->commands;
        return CommandResource::collection($commands);
    }

    //Search User
    public function searchUser(Request $request)
    {
        $search = $request->has('search') ? $request->input('search') : "";
        $status = $request->has('status') ? $request->input('status') : "";
        //recherche des patners en fonction du paramètre:
        $users = User::users()->where('status', 'like', $status . "%")
            ->where(function ($q) use ($search) {

                $q->Where('email', 'LIKE', "%{$search}%")
                    ->orWhere('phone', 'LIKE', "%{$search}%");
            })
            ->get();

        return response()->json($users);
    }
// get user stats
    public function userStats()
    {
        $savedMoney = 0;
        $commands = User::find(auth()->user()->id)->commands()->where("status", "SUCCESS")->get();
        $commandsList = [];
        $boxs = [];

        foreach ($commands as $command) {
            $orderBoxs = BoxCommand::where("command_id", "=", $command->id)->get();
            array_push($commandsList, $orderBoxs[0]);
        };
        foreach ($commandsList as $command) {
            $orderBox = box::find($command->box_id);
            $savedMoney += $orderBox->oldprice - $orderBox->newprice; // saved money calculation

            array_push($boxs, $orderBox);
        };


        return response()->json([
            // "commandsList "=>$commandsList,
            // "boxs "=>$boxs,
            "savedMoney" => $savedMoney,
            "savedBoxs" => count($boxs)
        ]);
    }



        //Search User
        public function ratePartner(Request $request)
        {
            $validator = Validator::make($request->all(), [
                'partner_id' => 'required|exists:partners,id',
                'comment' => 'required|string',
                'rating' => 'required|int|max:3',
                
            ]);
    
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }


           $rate = new Rating;
            $rate->user_id =Auth::user()->id ;
            $rate->partner_id =$request->input('partner_id') ;
            $rate->comment =$request->input('comment') ;
            $rate->rating =$request->input('rating') ;
            $rate->save();
            return response()->json($rate);
        }
        
        //Search User
        public function getUsersRates()
        {

        $rates = User::find(Auth::user()->id)->partnerRating()->select('id','partner_id','rating','comment')->get();
            return response()->json($rates);
        }


}
