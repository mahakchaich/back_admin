<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommandeController;
use App\Http\Controllers\CommandePanierController;
use App\Http\Controllers\PanierController;
use App\Http\Controllers\UtilisateurController;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

//admin
Route::prefix('admin')->group(function () {
    Route::post('register', [AuthController::class, 'register']);


    Route::middleware(['auth:sanctum', 'scope.admin'])->group(function () {
        Route::post('login', [AuthController::class, 'login']);
        Route::get('user', [AuthController::class, 'user']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::put('users/info', [AuthController::class, 'updateInfo']);
        Route::put('users/password', [AuthController::class, 'updatePassword']);
        Route::get('utilisateur', [UtilisateurController::class, 'index']);
    });
});


//user


//Utilisateur
//Get all Utilisateurs
Route::get('utilisateurs', [UtilisateurController::class, 'getUtilisateur']);
//Get Utilisateur By Id
Route::get('utilisateur/{id}', [UtilisateurController::class, 'getUtilisateurById']);
//Add Utilisateur
Route::post('addutilisateur', [UtilisateurController::class, 'addUtilisateur']);
//Update Utilisateur By Id
Route::put('updateutilisateur/{id}', [UtilisateurController::class, 'updateUtilisateur']);
//Delete Utilisateur By Id
Route::delete('deleteutilisateur/{id}', [UtilisateurController::class, 'deleteUtilisateur']);

//Panier
Route::apiResource('paniers', PanierController::class);

//Commande
Route::get('commandes', [CommandeController::class, 'index']);
Route::get('commande/{id}', [CommandeController::class, 'commande']);
Route::get('getcommandefinal/{id}', [CommandeController::class, 'getcommandefinal']);

//Les Paniers Commandés
Route::get('commandepanier', [CommandePanierController::class, 'index']);
