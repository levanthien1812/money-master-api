<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Helpers\MyResponse;
use App\Http\Requests\Users\LoginRequest;
use App\Http\Requests\Users\RegisterUserRequest;
use App\Http\Requests\Users\ResetPasswordRequest;
use App\Http\Requests\Users\SendVerficationCodeRequest;
use App\Http\Services\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(private AuthService $authService)
    {
    }

    public function register(RegisterUserRequest $request)
    {
        $returnData = $this->authService->register($request->all());

        return (new MyResponse($returnData))->get();
    }

    public function sendVerificationCode(SendVerficationCodeRequest $request)
    {
        $returnData = $this->authService->sendVerificationCode($request->email);

        return (new MyResponse($returnData))->get();
    }

    public function verify(Request $request)
    {
        $returnData = $this->authService->verify($request->email, $request->verification_code);

        return (new MyResponse($returnData))->get();
    }

    public function login(LoginRequest $request)
    {
        $returnData = $this->authService->login($request->all());

        return (new MyResponse($returnData))->get();
    }

    public function forgetPassword(Request $request)
    {
        $returnData = $this->authService->forgetPassword($request->email);

        return (new MyResponse($returnData))->get();
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        $returnData = $this->authService->resetPassword($request->all());

        return (new MyResponse($returnData))->get();
    }

    public function logout(Request $request)
    {
        $returnData = $this->authService->logout($request->user());

        return (new MyResponse($returnData))->get();
    }
}
