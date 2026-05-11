<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\ApiToken;
use App\Helpers\ApiResponse;

class ApiAuth
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return ApiResponse::error('Unauthorized, No Token Provided', 401);
        }

        $hashed = hash('sha256', $token);

        $record = ApiToken::where('token', $hashed)
            ->where(function ($q) {
                $q->whereNull('expired_at')
                    ->orWhere('expired_at', '>', now());
            })
            ->first();

        if (!$record) {
            return ApiResponse::error('Unauthorized, Invalid Token', 401);
        }

        $request->merge(['auth_user_id' => $record->user_id]);

        return $next($request);
    }
}