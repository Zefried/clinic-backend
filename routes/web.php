<?php
use App\Services\AuthService;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


// Route::get('/test-auth', function () {
//     $authService = new AuthService();
//     return response()->json(['message' => 'AuthService instantiated successfully.']);
// });