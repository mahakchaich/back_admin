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
<<<<<<< HEAD
    common('scope.admin');
    Route::middleware(['auth:sanctum', 'scope.admin'])->group(function () {
        //User Management
        Route::get('users', [UtilisateurController::class, 'getusers']);
        Route::get('user/{id}', [UtilisateurController::class, 'getUserById']);
        Route::post('adduser', [UtilisateurController::class, 'addUser']);
        Route::put('updateuser/{id}', [UtilisateurController::class, 'updateUser']);
        Route::delete('deleteuser/{id}', [UtilisateurController::class, 'deleteUser']);
=======
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);


    Route::middleware(['auth:sanctum', 'scope.admin'])->group(function () {
        Route::get('user', [AuthController::class, 'user']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::put('users/info', [AuthController::class, 'updateInfo']);
        Route::put('users/password', [AuthController::class, 'updatePassword']);
        Route::get('utilisateur', [UtilisateurController::class, 'index']);
>>>>>>> a165a3fa0ca3e0f898182721987280afb2b8c717
    });
});





//User
Route::prefix('user')->group(function () {
    common('scope.user');
});


//Partenaire



//Panier
Route::apiResource('paniers', PanierController::class);

//Commande
Route::get('commandes', [CommandeController::class, 'index']);
Route::get('commande/{id}', [CommandeController::class, 'commande']);
Route::get('getcommandefinal/{id}', [CommandeController::class, 'getcommandefinal']);

//Les Paniers Commandés
Route::get('commandepanier', [CommandePanierController::class, 'index']);
Route::get('test', [AuthController::class, 'test']);
