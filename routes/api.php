<?php

use App\Http\Controllers\AccountRequestController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LabController;
use App\Http\Controllers\patientController\PatientRegRequestController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\SimpleAuthController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\UserMiddleware;
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

Route::post('/doc-login', [SimpleAuthController::class, 'docPharmaLogin']);
Route::post('/lab-login', [SimpleAuthController::class, 'labLogin']);

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

    // Route for direct lab registration 
    Route::post('/admin/add-lab', [LabController::class, 'addLab']);
    Route::get('/admin/fetch-lab-account-data', [LabController::class, 'fetchLabData']);
    Route::get('/admin/fetch-lab-single-account-data/{id}', [LabController::class, 'fetchSingleLabData']);
    Route::get('/admin/auto-search', [LabController::class, 'autoSearch']);


    Route::get('/admin/auto-search-user', [PatientRegRequestController::class, 'autoSearchUser']);
    Route::post('/admin/fetch-all-patient/', [PatientRegRequestController::class, 'fetchingAllPatient']);
    Route::post('/admin/fetch-all-pending-patient/', [PatientRegRequestController::class, 'fetchingUserSpecificPatient']);
   
 
});


// - // working with User protected routes // - //
Route::middleware(['auth:sanctum', UserMiddleware::class, AdminMiddleware::class])->group(function(){

    // Route for Patient registration request 
    Route::post('/user/add-patient-request', [PatientRegRequestController::class, 'addPatientRequest']);
    Route::get('/user/fetch-xuser-patient/', [PatientRegRequestController::class, 'fetchXUserPatient']);
    Route::get('/user/fetch-xuser-pending-patient/', [PatientRegRequestController::class, 'fetchXUserPendingPatient']);


});


