<?php

use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PanierController;
use App\Http\Controllers\CommandeController;
use App\Http\Controllers\UtilisateurController;
use App\Http\Controllers\CommandePanierController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

function common(string $scope)
{
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::middleware(['auth:sanctum', $scope])->group(
        function () {
            Route::get('user', [AuthController::class, 'user']);
            Route::post('logout', [AuthController::class, 'logout']);
            Route::put('users/info', [AuthController::class, 'updateInfo']);
            Route::put('users/password', [AuthController::class, 'updatePassword']);
        }
    );
}

//admin
Route::prefix('admin')->group(function () {
    common('scope.admin');
    Route::middleware(['auth:sanctum', 'scope.admin'])->group(function () {
        //User Management
        Route::apiResource('users', UserController::class);
        //Panier Management
        Route::apiResource('paniers', PanierController::class);
    });
});





//User
Route::prefix('user')->group(function () {
    common('scope.user');
});


//Partenaire





//Commande
Route::get('commandes', [CommandeController::class, 'index']);
Route::get('commande/{id}', [CommandeController::class, 'commande']);
Route::get('getcommandefinal/{id}', [CommandeController::class, 'getcommandefinal']);

//Les Paniers Commandés
Route::get('commandepanier', [CommandePanierController::class, 'index']);
Route::get('test', [AuthController::class, 'test']);
