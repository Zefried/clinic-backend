<?php

use App\Http\Controllers\AccountRequestController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmployeeController\EmployeeController;
use App\Http\Controllers\LabController;
use App\Http\Controllers\LabMasterController\TestCategoryController;
use App\Http\Controllers\LabMasterController\TestController;
use App\Http\Controllers\LabTestController\InsertTestInLab;
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








// - // Project routes starts from here above are from different project - copied // - //



// - // working with public routes // - //
Route::post('/admin/register-admin', [RegisterController::class, 'registerAdmin']);
Route::post('/admin-login', [SimpleAuthController::class, 'adminLogin']);
Route::post('/account-request-submission', [AccountRequestController::class, 'requestSubmission'])->name('requestSubmission');

Route::post('/doc-login', [SimpleAuthController::class, 'docPharmaLogin']);
Route::post('/lab-login', [SimpleAuthController::class, 'labLogin']);
Route::post('/hospital-login', [SimpleAuthController::class, 'hospitalLogin']);







// - // working with admin protected routes // - //
Route::middleware(['auth:sanctum', AdminMiddleware::class])->group(function(){





    // Route for direct doctor and worker registration 
    Route::post('/admin/add-doctor', [RegisterController::class, 'adminDoctorRegistration']);
    Route::post('/admin/fetch-doctor', [RegisterController::class, 'fetchDoctors']);
    Route::post('/admin/fetch-doctor/{id}', [RegisterController::class, 'fetchSingeDoctor']);
    Route::get('/admin/fetch-doctorCred/{Id}', [RegisterController::class, 'fetchDoctorsCred']);
    Route::post('/admin/change-doctorPsw/{Id}', [RegisterController::class, 'changeDocPsw']);
    Route::post('/admin/update-doctor/{Id}', [RegisterController::class, 'updateDoctorData']);
    Route::get('/admin/disable-doctor/{Id}', [RegisterController::class, 'disableDoctorData']);
  
        // Route for autoSearch doc and workers account 
        Route::get('/admin/search-users', [RegisterController::class, 'autoSearchUser']);

    // ends here  
    
    



    // Route for doctor and worker request applications 
    Route::get('/admin/fetch-pending-accounts', [AccountRequestController::class, 'fetchPendingAccounts']);
    Route::post('/admin/update-pending-accounts-info/{id}', [AccountRequestController::class, 'updatePendingAccountInfo']);
    Route::post('/admin/accept-pending-account', [AccountRequestController::class, 'acceptPendingAccount']);





    // Route for direct lab registration 
    Route::post('/admin/add-lab', [LabController::class, 'addLab']);
    Route::get('/admin/fetch-lab-account-data', [LabController::class, 'fetchLabData']);
    Route::get('/admin/fetch-lab-single-account-data/{id}', [LabController::class, 'fetchSingleLabData']);
    Route::post('/admin/update-lab-data/{id}', [LabController::class, 'updateLabUser']);
    Route::get('/admin/disable-lab/{Id}', [LabController::class, 'disableLabData']);
    Route::post('/admin/change-lab-psw/{id}', [LabController::class, 'changeLabPsw']);
    
       
        // Route for autoSearch doc and workers account 
        Route::get('/admin/lab-search', [LabController::class, 'labSearch']);
    // ends here    




    // Route for admin searching pending patient against specific doctor worker
    Route::get('/admin/auto-search-user', [PatientRegRequestController::class, 'autoSearchUser']);
    Route::post('/admin/fetch-all-patient/', [PatientRegRequestController::class, 'fetchingAllPatient']);
    Route::post('/admin/fetch-all-pending-patient/', [PatientRegRequestController::class, 'fetchingUserSpecificPatient']);
    



    ///////// Admin Masters Routes starts here

            // Route for admin adding diagnostic test master 
            Route::post('/admin/add-test-category', [TestCategoryController::class, 'addTestCategory']);
            Route::post('/admin/add-lab-test', [TestController::class, 'addTest']);
            Route::get('/admin/fetch-test-category', [TestCategoryController::class, 'fetchTestCategory']);
            Route::get('/admin/fetch-test/{id}', [TestController::class, 'fetchTestWithId']);
            Route::get('/admin/edit-test-category/{id}', [TestCategoryController::class, 'editTestCategory']);
            Route::post('/admin/update-test-category/{id}', [TestCategoryController::class, 'updateTestCategory']);
            Route::get('/admin/edit-lab-test/{id}', [TestController::class, 'editLabTest']);
            Route::post('/admin/update-lab-test/{id}', [TestController::class, 'updateLabTest']);

            // Route for admin adding employee against labs
            Route::post('/admin/add-employee/{id}', [EmployeeController::class, 'addEmployeeAgainstLab']);
            Route::get('/admin/fetch-lab-employee', [EmployeeController::class, 'fetchEmployee']);
            Route::post('/admin/fetch-specific-lab-employees', [EmployeeController::class, 'fetchSpecificLabEmployees']);

    
    ///////// Ends here






    ///////// Inserting test in lab, flow - Routes starts here

            //////// Inserting tests 
            Route::post('/admin/insert-test-in-lab', [InsertTestInLab::class, 'insertLabTest']);
            Route::get('/admin/view-assigned-categories/{id}', [InsertTestInLab::class, 'ViewAssignedCategories']);
           

            ///////// View Tests 
            Route::post('/admin/view-assigned-test/', [InsertTestInLab::class, 'ViewAssignedTest']);
            Route::get('/admin/view-all-test-lab/{id}', [InsertTestInLab::class, 'ViewAllTestOfLab']);

            //////// Removing tests 
            Route::post('/admin/remove-lab-test/', [InsertTestInLab::class, 'RemoveLabTest']);

            ///////// Search Test 
            Route::get('/admin/search-test-lab/{id}', [InsertTestInLab::class, 'searchTestOfLab']);



    ///////// Ends here


});












// - // working with User protected routes // - //
Route::middleware(['auth:sanctum', UserMiddleware::class])->group(function(){

    // Route for Patient registration request 
    Route::post('/user/add-patient-request', [PatientRegRequestController::class, 'addPatientRequest']);
    Route::get('/user/fetch-xuser-patient/', [PatientRegRequestController::class, 'fetchXUserPatient']);
    Route::get('/user/fetch-xuser-pending-patient/', [PatientRegRequestController::class, 'fetchXUserPendingPatient']);


});


