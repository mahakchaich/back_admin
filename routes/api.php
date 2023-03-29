<?php




use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BoxController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CommandController;
use App\Http\Controllers\PartnerController;

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
    Route::post('registerpartner', [PartnerController::class, 'store']);
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
        // role management
        //Boxs Management
        Route::apiResource('boxs', BoxController::class);
        Route::get('searchBoxs', [BoxController::class, 'searchBoxs']);
        Route::get('boxs/boxdetails/{id}', [BoxController::class, 'boxdetails']);
        Route::get('searchBox', [BoxController::class, 'searchBox']);
        Route::get('filterboxs', [BoxController::class, 'filterBoxs']);
        Route::post('updateBox/{id}', [BoxController::class, 'updateBox']);
        //Users Management
        Route::apiResource('users', UserController::class);
        Route::get('getuser/{id}', [UserController::class, 'getUserById']);
        Route::get('searchUsers', [UserController::class, 'searchUsers']);
        Route::put('users/status/{id}', [UserController::class, 'updateUserStatus']);
        Route::get('/users/userdetails/{id}', [UserController::class, 'showuser']);
        Route::get('searchUser', [UserController::class, 'searchUser']);
        Route::get('filterusers', [UserController::class, 'filterUsers']);

        //Orders Management
        Route::get('orders', [CommandController::class, 'getOrder']);
        Route::get('orders/getorder/{id}', [CommandController::class, 'getOrderById']);
        Route::post('orders/addorder', [CommandController::class, 'addOrder']);
        Route::put('orders/updateorder/{id}', [CommandController::class, 'updateOrder']);
        Route::delete('orders/deleteorder/{id}', [CommandController::class, 'deleteOrder']);
        Route::get('orders/orderdetails', [CommandController::class, 'index']);
        Route::get('orders/orderdetails/{id}', [CommandController::class, 'show']);
        Route::get('searchOrder', [CommandController::class, 'searchOrder']);
        Route::get('filterorders', [CommandController::class, 'filterOrders']);

        //Partners Management
        Route::apiResource('partners', PartnerController::class);
        Route::get('partners/partnerdetails/{id}', [PartnerController::class, 'showdetails']);
        Route::get('searchPartner', [PartnerController::class, 'searchPartner']);
        Route::get('filter', [PartnerController::class, 'filterPartners']);
        Route::post('update/{id}', [PartnerController::class, 'updatePartner']);
    });
});





//User
Route::prefix('user')->group(function () {
    common('scope.user');
    Route::middleware(['auth:sanctum', 'scope.user'])->group(function () {
        // Box
        Route::get('/boxs', [BoxController::class, 'index']); // all boxs
        Route::get('/boxs/{id}', [BoxController::class, 'show']); // get single box
        Route::get('/showboxs', [BoxController::class, 'index2']);

        // Like
        Route::post('/boxs/{id}/likes', [LikeController::class, 'likeOrUnlike']);
    });

    // order
    Route::get('/orders', [CommandController::class, 'index']);
    Route::post('/orders', [CommandController::class, 'store']);
    Route::get('/orders/{id}', [CommandController::class, 'show']);
    Route::put('/orders/{id}', [CommandController::class, 'update']);
    Route::delete('/orders/{id}', [CommandController::class, 'destroy']);
});


//Partenaire
Route::prefix('partner')->group(function () {
    common('scope.partner');
    Route::middleware(['auth:sanctum', 'scope.partner'])->group(function () {
        //Box
        Route::apiResource('boxs', BoxController::class);

        Route::put('partners/info', [AuthController::class, 'update']);
    });
});




//Commande
Route::get('commandes', [CommandController::class, 'index']);
Route::get('commande/{id}', [CommandController::class, 'commande']);





//
Route::post('forgetPassWord', [UserController::class, 'forgetPassWord']);
Route::put('forgetPassWord', [UserController::class, 'forgetPassWordReset']);
Route::put('addRole', [UserController::class, "addRole"]);
