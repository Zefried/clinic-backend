<?php

namespace App\Http\Controllers;
use App\Services\AuthService;

use Illuminate\Http\Request;


class AuthController extends Controller
{
    // test controller no uses in real projects 

    protected $authService;

    public function __construct(authService $authService){
        $this->authService = $authService;
    }

    public function handleCallback($provider){
        return $this->authService->handleCallback($provider);
    }

    public function register(Request $request)
    {
        return $this->authService->register($request);
    }

}
