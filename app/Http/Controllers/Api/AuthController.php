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
        $this->middleware('api.auth')->only(['me', 'checkPin']);
        $this->middleware('superadmin')->only(['checkPin']);
    }

    /**
     * @OA\Post(
     * path="/api/login",
     * operationId="authLogin",
     * tags={"Authentication"},
     * summary="User Login",
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * required={"username","password"},
     * @OA\Property(property="username", type="string", example="superadmin"),
     * @OA\Property(property="password", type="string", format="password", example="password")
     * )
     * ),
     * @OA\Response(response=200, description="Login berhasil"),
     * @OA\Response(response=401, description="Unauthorized")
     * )
     */
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

    /**
     * @OA\Get(
     * path="/api/me",
     * operationId="getAuthUser",
     * tags={"Authentication"},
     * summary="Get Current User Profile",
     * description="Mengambil data profil user yang sedang login menggunakan token.",
     * security={{"bearerAuth":{}}},
     * @OA\Response(
     * response=200,
     * description="Get user success",
     * @OA\JsonContent(
     * @OA\Property(property="success", type="boolean", example=true),
     * @OA\Property(property="message", type="string", example="Get user success"),
     * @OA\Property(property="data", type="object",
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="name", type="string", example="Achmad Fawaid"),
     * @OA\Property(property="username", type="string", example="admin"),
     * @OA\Property(property="email", type="string", example="admin@example.com"),
     * @OA\Property(property="role", type="string", example="superadmin")
     * )
     * )
     * ),
     * @OA\Response(
     * response=401, 
     * description="Unauthenticated"
     * )
     * )
     */
    public function me(Request $request)
    {
        $userId = $request->get('auth_user_id');

        $user = $this->service->getUser($userId);

        return ApiResponse::success($user, 'Get user success');
    }

    /**
     * @OA\Post(
     * path="/api/check-pin",
     * operationId="authCheckPin",
     * tags={"Authentication"},
     * summary="Validasi PIN User",
     * description="Mencocokkan input PIN 6 digit (Khusus Role Superadmin).",
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * required={"pin"},
     * @OA\Property(property="pin", type="string", minLength=6, maxLength=6, example="123456")
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="PIN Valid",
     * @OA\JsonContent(
     * @OA\Property(property="success", type="boolean", example=true),
     * @OA\Property(property="message", type="string", example="PIN valid"),
     * @OA\Property(property="data", type="boolean", example=true)
     * )
     * ),
     * @OA\Response(
     * response=401, 
     * description="Unauthenticated"
     * ),
     * @OA\Response(
     * response=403, 
     * description="PIN Salah / Forbidden"
     * )
     * )
     */
    public function checkPin(Request $request)
    {
        try {
            $request->validate([
                'pin' => 'required|digits:6'
            ]);

            $userId = $request->get('auth_user_id');

            $result = $this->service->checkPin($userId, $request->pin);

            return ApiResponse::success($result, 'PIN valid');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 403);
        }
    }
}