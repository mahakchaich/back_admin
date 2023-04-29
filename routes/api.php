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
    Route::put('passwordeux', [AuthController::class, 'updatePasswordeux']);
    Route::put('passwordpartner', [AuthController::class, 'updatePassworPartner']);
    Route::post('registerpartner', [PartnerController::class, 'store']);
    Route::post('login', [AuthController::class, 'login']);



    Route::middleware(['auth:sanctum', $scope])->group(
        function () {
            Route::post('logout', [AuthController::class, 'logout']);
            Route::get('user', [AuthController::class, 'user']);
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
        Route::get('user', [AuthController::class, 'user']);
        Route::apiResource('users', UserController::class);
        Route::put('user/password', [AuthController::class, 'updatePassword']);

        // Box
        Route::get('boxs', [BoxController::class, 'index']); // all boxs
        Route::get('availableBoxs', [BoxController::class, 'availableBoxs']);

        Route::get('boxs/boxdetails/{id}', [BoxController::class, 'boxdetails']);
        Route::get('boxs/favorites', [BoxController::class, 'getfavorsBoxs']);

        Route::get('indexByCategory/{category}', [BoxController::class, 'indexByCategory']);
        Route::get('/boxs/{id}', [BoxController::class, 'show']); // get single box
        Route::get('/showboxs', [BoxController::class, 'index2']);
        // order
        Route::post('orders/addorder', [CommandController::class, 'addOrder']);

        // Like
        Route::post('/boxs/{id}/likes', [LikeController::class, 'likeOrUnlike']);
        Route::get('/boxs/{id}/checklikes', [LikeController::class, 'verifLike']);
    });
});


//Partenaire
Route::prefix('partner')->group(function () {
    common('scope.partner');
    Route::middleware(['auth:sanctum', 'scope.partner'])->group(function () {
        Route::get('user', [PartnerController::class, 'currentPartner']);
        Route::put('changepassword', [PartnerController::class, 'changePassword']);
        Route::post('logout', [PartnerController::class, 'logout']);
        //Box
        Route::apiResource('boxs', BoxController::class);
        //
        Route::get('partnerboxes', [PartnerController::class, 'showpartnerboxes']);

        Route::put('partners/info', [AuthController::class, 'update']);
        Route::get('getPartnerBoxs', [PartnerController::class, 'getPartnerBoxs']);
        Route::get('getPartnerBoxsAccepted', [PartnerController::class, 'getPartnerBoxsAccepted']);
        Route::get('getPartnerBoxsPending', [PartnerController::class, 'getPartnerBoxsPending']);
        Route::get('getPartnerBoxsRejected', [PartnerController::class, 'getPartnerBoxsRejected']);
        Route::get('getPartnerBoxsFinished', [PartnerController::class, 'getPartnerBoxsFinished']);
        Route::get('getPartnerBoxsExpired', [PartnerController::class, 'getPartnerBoxsExpired']);
        Route::get('getPartnerDetails', [PartnerController::class, 'showPartnerDetails']);
    });
});




//Commande
Route::get('commandes', [CommandController::class, 'index']);
Route::get('commande/{id}', [CommandController::class, 'commande']);





//
Route::post('forgetPassWord', [UserController::class, 'forgetPassWord']);
Route::post('verifCode', [UserController::class, 'verifCode']);
Route::put('addRole', [UserController::class, "addRole"]);
