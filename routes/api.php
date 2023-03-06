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
        //Paniers Management
        Route::apiResource('paniers', PanierController::class);
        //Users Management
        Route::apiResource('users', UserController::class);
        Route::get('getuser/{id}', [UserController::class, 'getUserById']);
        Route::get('searchUsers', [UserController::class, 'searchUsers']);
        //Orders Management
        Route::get('orders', [CommandeController::class, 'getOrder']);
        Route::get('orders/getorder/{id}', [CommandeController::class, 'getOrderById']);
        Route::post('orders/addorder', [CommandeController::class, 'addOrder']);
        Route::put('orders/updateorder/{id}', [CommandeController::class, 'updateOrder']);
        Route::delete('orders/deleteorder/{id}', [CommandeController::class, 'deleteOrder']);
        Route::get('orders/orderdetails/{id}', [CommandeController::class, 'commandedetails']);
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



// //Les Paniers Commandés
// Route::get('commandepanier', [CommandePanierController::class, 'index']);
// Route::get('test', [AuthController::class, 'test']);


//
Route::post('forgetPassWord', [UserController::class, 'forgetPassWord']);
Route::put('forgetPassWord', [UserController::class, 'forgetPassWordReset']);
