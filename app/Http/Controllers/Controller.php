<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 * version="1.0.0",
 * title="API Documentation",
 * description="Dokumentasi API Terintegrasi menggunakan L5-Swagger",
 * @OA\Contact(email="developer@example.com")
 * )
 * * @OA\Server(
 * url=L5_SWAGGER_CONST_HOST,
 * description="API Server Environment"
 * )
 * * @OA\SecurityScheme(
 * securityScheme="bearerAuth",
 * type="http",
 * scheme="bearer",
 * bearerFormat="JWT",
 * description="Masukkan token Anda tanpa kata 'Bearer '"
 * )
 */

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
