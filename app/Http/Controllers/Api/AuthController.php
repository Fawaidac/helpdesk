<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    protected AuthService $service;

    public function __construct()
    {
        $this->service = new AuthService();
        $this->middleware('api.auth')->only(['me']);
    }

    public function login(Request $request)
    {
        try {
            $request->validate([
                'username' => 'required',
                'password' => 'required'
            ]);

            $data = $this->service->login(
                $request->username,
                $request->password
            );

            return ApiResponse::success($data, 'Login berhasil');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 401);
        }
    }

    public function me(Request $request)
    {
        $userId = $request->get('auth_user_id');

        $user = $this->service->getUser($userId);

        return ApiResponse::success($user, 'Get user success');
    }
}
