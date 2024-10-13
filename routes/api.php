<?php

use App\Http\Controllers\AccountRequestController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\SimpleAuthController;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;




// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');


// Route::post('/admin/testMw', [SimpleAuthController::class, 'testMw'])
//     ->middleware(AdminMiddleware::class)->middleware('auth:sanctum')
//     ->name('login');



// Route to handle the callback from Google || from other projects but we might need it
Route::get('auth/{provider}/callback', [AuthController::class, 'handleCallBack'])->name('handleCallBack');
Route::post('admin/add-team', [AdminController::class, 'addTeam'])->name('addTeam');
Route::post('admin/verify-team', [AdminController::class, 'verifyTeam'])->name('verifyTeam');


// - // Project routes starts from here above are from different project - copied // - //

// - // working with public routes // - //
Route::post('/admin/register-admin', [RegisterController::class, 'registerAdmin']);
Route::post('/admin-login', [SimpleAuthController::class, 'adminLogin']);
Route::post('/account-request-submission', [AccountRequestController::class, 'requestSubmission'])->name('requestSubmission');


// - // working with admin protected routes // - //
Route::middleware(['auth:sanctum', AdminMiddleware::class])->group(function(){

    // Route for direct doctor and pharmacist registration 
    Route::post('/admin/add-doctor', [RegisterController::class, 'adminDoctorRegistration']);
    Route::post('/admin/fetch-doctor', [RegisterController::class, 'fetchDoctors']);
    Route::post('/admin/fetch-doctor/{id}', [RegisterController::class, 'fetchSingeDoctor']);
    Route::get('/admin/fetch-doctorCred/{Id}', [RegisterController::class, 'fetchDoctorsCred']);
    Route::post('/admin/change-doctorPsw/{Id}', [RegisterController::class, 'changeDocPsw']);
    Route::post('/admin/update-doctor/{Id}', [RegisterController::class, 'updateDoctorData']);


    // Route for doctor and pharmacist request applications 
    Route::get('/admin/fetch-pending-accounts', [AccountRequestController::class, 'fetchPendingAccounts']);
    Route::post('/admin/update-pending-accounts-info/{id}', [AccountRequestController::class, 'updatePendingAccountInfo']);
    Route::post('/admin/accept-pending-account', [AccountRequestController::class, 'acceptPendingAccount']);

});


