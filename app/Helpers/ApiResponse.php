<?php

namespace App\Helpers;

class ApiResponse
{
    public static function success($data = null, $message = 'Success', $code = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'code' => $code,
            'data' => $data
        ], $code);
    }

    public static function error($message = 'Error', $code = 400, $data = null)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'code' => $code,
            'data' => $data
        ], $code);
    }
}