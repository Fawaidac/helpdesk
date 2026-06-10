<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;
use App\Helpers\ApiResponse;

class SuperAdminOnly
{
    public function handle(Request $request, Closure $next)
    {
        $userId = $request->get('auth_user_id');

        if (!$userId) {
            return ApiResponse::error('Unauthorized', 401);
        }

        $user = User::find($userId);

        if (!$user) {
            return ApiResponse::error('User not found', 404);
        }

        if ($user->role !== 'superadmin') {
            return ApiResponse::error('Forbidden, Super Admin Only', 403);
        }

        return $next($request);
    }
}